<?php

namespace App\Http\Controllers\ChannelPartners;

use DB;
use Config;
use App\Models\User;
use App\Models\Company;
use App\Models\Parameter;
use App\Models\ProductGroup;
use App\Models\UserDiscount;
use Illuminate\Http\Request;
use App\Models\ChannelPartner;
use App\Models\ProductInventory;
use App\Http\Controllers\Controller;
use App\Models\CreditTranscationLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\UserLog;
use App\Models\CityList;
use App\Models\StateList;
use App\Models\CountryList;
use App\Models\LeadTimeline;


class ChannelPartnersController extends Controller
{
    public function __construct()
	{

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(0, 1, 2, 6, 9, 13);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}

			return $next($request);
		});
	}
    public function index(Request $request)
    {
        $data = array();
		$data['title'] = "Channel Partners";
        $data['id'] = isset($request->id) ? $request->id : 0;
		$data['type'] = 101;
		$data['isSalePerson'] = isSalePerson();
		$data['addView'] = (isset($request->add) && $request->add == 1) ? 1 : 0;
		$data['isRequest'] = 0;
		$data['is_new_channel_partner_module'] = 1;
		$data['viewMode'] = (isset($request->view_mode) && $request->view_mode == 1) ? 1 : 0;

		if ($data['isSalePerson'] == 1) {
			$data['viewMode'] = 0;
		}
        return view('channel_partners_new/index', compact('data'));
    }

	public function getListAjax(Request $request)
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
        $isChannelPartner = isChannelPartner(Auth::user()->type);
        $isCreUser = isCreUser();
        $isMarketingDispatcherUser = isMarketingDispatcherUser();
        $isTaleSalesUser = isTaleSalesUser();
        if ($isTaleSalesUser == 1) {
            $TeleSalesCity = TeleSalesCity(Auth::user()->id);
        }

        if ($isSalePerson == 1) {
            $SalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
        }

        $searchColumns = array(
			
			0 => 'users.id',
			1 => 'users.first_name',
			2 => 'users.last_name',
			3 => 'users.email',
			4 => 'users.phone_number',
			5 => 'channel_partner.gst_number',
			6 => 'channel_partner.firm_name',
			7 => 'reporting_channel_partner.firm_name',

		);

        $sortingColumns = [
            0 => 'users.id',
			1 => 'users.first_name',
			2 => 'users.email',
			3 => 'channel_partner.reporting_company_id',
			4 => 'channel_partner.sale_persons',
			5 => 'users.last_active_date_time',
        ];

        $selectColumns = ['users.id', 'users.type', 'users.first_name', 'users.last_name', 'users.email', 'users.phone_number', 'users.last_active_date_time', 'users.last_login_date_time', 'channel_partner.sale_persons', 'users.status', 'channel_partner.gst_number', 'channel_partner.reporting_manager_id', 'channel_partner.reporting_company_id', 'channel_partner.tele_verified', 'channel_partner.tele_not_verified', 'channel_partner.firm_name', 'channel_partner.data_verified', 'channel_partner.data_not_verified', 'channel_partner.missing_data'];


        $sortingColumns = array();
        $isorderby = 0;

        $search_value = '';

		$query = DB::table('users');
		$query->leftJoin('channel_partner', 'channel_partner.id', '=', 'users.reference_id');
		$query->leftJoin('channel_partner as reporting_channel_partner', 'channel_partner.reporting_manager_id', '=', 'reporting_channel_partner.user_id');
		if ($isRequest == 1) {
			$query->where('users.status', 2);
			$query->where('users.type', '>=', $request->type);
		} else {
			$query->where('users.type', $request->type);
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
		} else if ($isTaleSalesUser == 1) {
			$query->whereIn('users.city_id', $TeleSalesCity);
		}

        $Filter_lead_ids = $query->distinct()->pluck('users.id');

        $recordsTotal = $Filter_lead_ids->count();
        $recordsFiltered = $recordsTotal;

        // RECORDSFILTERED START
		$query = DB::table('users');
		$query->select($selectColumns);
		$query->leftJoin('channel_partner', 'channel_partner.id', '=', 'users.reference_id');
		$query->leftJoin('channel_partner as reporting_channel_partner', 'channel_partner.reporting_manager_id', '=', 'reporting_channel_partner.user_id');
		$query->whereIn('users.id', $Filter_lead_ids);
        $search_value = '';
		
		if (isset($request['search']['value'])) {
            $isFilterApply = 1;
            $search_value = $request['search']['value'];
            $query->where(function ($query) use ($search_value, $searchColumns) {
                for ($i = 0; $i < count($searchColumns); $i++) {
                    if ($i == 0) {
                        $query->whereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', ['%' . $search_value . '%']);
                    } else {
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', ['%' . $search_value . '%']);
                    }
                }
            });
        }

        $recordsFiltered = $query->count();

        // RECORDSFILTERED START

        $query = DB::table('users');
		$query->select($selectColumns);
		$query->leftJoin('channel_partner', 'channel_partner.id', '=', 'users.reference_id');
		$query->leftJoin('channel_partner as reporting_channel_partner', 'channel_partner.reporting_manager_id', '=', 'reporting_channel_partner.user_id');
		$query->whereIn('users.id', $Filter_lead_ids);
        $query->limit($request->length);
        $query->offset($request->start);
		if ($isorderby == 0) {
            $query->orderBy('users.id', 'DESC');
        }else {
            foreach ($sortingColumns as $key => $value) {
                $query->orderBy($value['column'], $value['sort']);
            }
        }
        $isFilterApply = 0;

		if (isset($request['search']['value'])) {
            $isFilterApply = 1;
            $search_value = $request['search']['value'];
            $query->where(function ($query) use ($search_value, $searchColumns) {
                for ($i = 0; $i < count($searchColumns); $i++) {
                    if ($i == 0) {
                        $query->whereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', ['%' . $search_value . '%']);
                    } else {
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', [$search_value]);
                        $query->orWhereRaw($searchColumns[$i] . ' like ? ', ['%' . $search_value . '%']);
                    }
                }
            });
        }

        
        $data = $query->get();
		
        $data = json_decode(json_encode($data), true);

        $viewData = [];
        foreach ($data as $key => $value) {
            $view = '';
            $view .= '<li class="lead_li" id="lead_' . $value['id'] . '" onclick="getDataDetail(' . $value['id'] . ')" style="list-style:none">';
            $view .= '<a href="javascript: void(0);">';
            $view .= '<div class="d-flex">';
            $view .= '<div class="flex-grow-1 overflow-hidden">';
           
        	$view .= '<h5 class="text-truncate font-size-14 mb-1">#' . highlightString($value['id'],$search_value) . ' -  ' . highlightString(getUserTypeName($value['type']),$search_value).'</h5>';

           
            $view .= '<p class="text-truncate mb-0">' . highlightString(ucwords(strtolower($value['first_name'])) . ' ' . ucwords(strtolower($value['last_name'])),$search_value) . '</p>';
            $view .= '</div>';
            $view .= '<div class="d-flex justify-content-end font-size-16">';
            $view .= '<span style="height: fit-content;">' . getUserStatusLable($value['status']) . '</span>';
            $view .= '</div>';
            $view .= '</div>';
            $view .= '</a>';
            $view .= '</li>';

            $viewData[$key] = [];
            $viewData[$key]['view'] = $view;
        }

        $jsonData = [
            'draw' => intval($request['draw']),
            // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => intval($recordsTotal),
            // total number of records
            'recordsFiltered' => intval($recordsFiltered),
            // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $viewData,
            // total data array
            'dataed' => $data,
            // total data array
            // 'FirstPageLeadId' => $FirstPageArchitectId,
        ];
        return $jsonData;
    }
	public function discountAjax(Request $request)
	{

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		if ($isAdminOrCompanyAdmin == 1) {

			$searchColumns = array(
				0 => 'product_inventory.id',
				1 => 'product_inventory.description',
				2 => 'product_brand.name',
				3 => 'product_code.name',

			);

			$sortingColumns = array(
				0 => 'product_brand.name',
				1 => 'product_code.name',
				2 => 'product_inventory.description',

			);

			$selectColumns = array(
				0 => 'product_inventory.id',
				1 => 'product_brand.name as product_brand_name',
				2 => 'product_code.name as product_code_name',
				3 => 'product_inventory.description',
				4 => 'user_discounts.discount_percentage',
				5 => 'user_discounts.id as user_discount_id',

			);

			if ($request->isLoadDiscountTable != 0) {

				if ($request->product_group_id != 0) {

					$productBrandIds = array(0);

					$ProductGroup = ProductGroup::select('product_brand')->find($request->product_group_id);
					if ($ProductGroup) {
						if ($ProductGroup->product_brand != "") {

							$productBrandIds = explode(",", $ProductGroup->product_brand);
						}
					}

					$recordsTotal = ProductInventory::whereIn('product_brand_id', $productBrandIds)->count();
				} else {
					$recordsTotal = ProductInventory::count();
				}

				$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows
				//DB::enableQueryLog();
				$userId = $request->user_id;
				$query = ProductInventory::query();
				if ($request->product_group_id != 0) {

					$productBrandIds = array(0);

					$ProductGroup = ProductGroup::select('product_brand')->find($request->product_group_id);
					if ($ProductGroup) {
						if ($ProductGroup->product_brand != "") {

							$productBrandIds = explode(",", $ProductGroup->product_brand);
						}
					}
					$query->whereIn('product_brand_id', $productBrandIds);
				}
				$query->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
				$query->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');
				$query->leftJoin('user_discounts', function ($join) use ($userId) {

					$join->on('user_discounts.product_inventory_id', '=', 'product_inventory.id');
					$join->on('user_discounts.user_id', '=', DB::raw($userId));
				});

				$query->select($selectColumns);
				$query->limit($request->length);
				$query->offset($request->start);
				$query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
				$isFilterApply = 0;

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
				// print_r(DB::getQueryLog());
				// die;

				$data = json_decode(json_encode($data), true);
				if ($isFilterApply == 1) {
					$recordsFiltered = count($data);
				}

				foreach ($data as $key => $value) {

					$data[$key]['product_brand'] = "<p>" . $value['product_brand_name'] . "</p>";
					$data[$key]['product_code'] = "<p>" . $value['product_code_name'] . "</p>";
					$data[$key]['description'] = "<p>" . $value['description'] . "</p>";
					if (!isset($value['user_discount_id']) || $value['user_discount_id'] == null) {
						$data[$key]['discount_percentage'] = 0;
						$value['user_discount_id'] = 0;
					} else {
						$data[$key]['discount_percentage'] = $value['discount_percentage'];
					}

					$data[$key]['new_discount_percentage'] = "<input type='number'   min='0' max='100' step='1' class='form-control new-discount-cls valid-discount' id='" . $request->user_id . "-" . $value['id'] . "-" . $value['user_discount_id'] . "' value='" . $data[$key]['discount_percentage'] . "'  />";
				}
			} else {

				$data = array();
				$recordsTotal = 0;
				$recordsFiltered = 0;
			}

			$jsonData = array(
				"draw" => intval($request['draw']),
				// for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
				"recordsTotal" => intval($recordsTotal),
				// total number of records
				"recordsFiltered" => intval($recordsFiltered),
				// total number of records after searching, if there is no searching then totalFiltered = totalData
				"data" => $data, // total data array

			);
			return $jsonData;
		} else {
			$jsonData = array(
				"draw" => 0,
				// for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
				"recordsTotal" => 0,
				// total number of records
				"recordsFiltered" => 0,
				// total number of records after searching, if there is no searching then totalFiltered = totalData
				"data" => array(), // total data array

			);
			return $jsonData;
		}
	}

	public function cptDiscountAjax(Request $request)
	{

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		if ($isAdminOrCompanyAdmin == 1) {

			$searchColumns = array(
				0 => 'product_inventory.id',
				1 => 'product_inventory.description',
				2 => 'product_brand.name',
				3 => 'product_code.name',

			);

			$sortingColumns = array(
				0 => 'product_brand.name',
				1 => 'product_code.name',
				2 => 'product_inventory.description',

			);

			$selectColumns = array(
				0 => 'product_inventory.id',
				1 => 'product_brand.name as product_brand_name',
				2 => 'product_code.name as product_code_name',
				3 => 'product_inventory.description'
			);


			if ($request->product_group_id != 0) {

				$productBrandIds = array(0);

				$ProductGroup = ProductGroup::select('product_brand')->find($request->product_group_id);
				if ($ProductGroup) {
					if ($ProductGroup->product_brand != "") {

						$productBrandIds = explode(",", $ProductGroup->product_brand);
					}
				}

				$recordsTotal = ProductInventory::whereIn('product_brand_id', $productBrandIds)->count();
			} else {
				$recordsTotal = ProductInventory::count();
			}

			$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows
			//DB::enableQueryLog();
			// $userId = $request->user_id;
			$query = ProductInventory::query();
			if ($request->product_group_id != 0) {

				$productBrandIds = array(0);

				$ProductGroup = ProductGroup::select('product_brand')->find($request->product_group_id);
				if ($ProductGroup) {
					if ($ProductGroup->product_brand != "") {

						$productBrandIds = explode(",", $ProductGroup->product_brand);
					}
				}
				$query->whereIn('product_brand_id', $productBrandIds);
			}
			$query->leftJoin('data_master as product_brand', 'product_brand.id', '=', 'product_inventory.product_brand_id');
			$query->leftJoin('data_master as product_code', 'product_code.id', '=', 'product_inventory.product_code_id');


			$query->select($selectColumns);
			$query->limit($request->length);
			$query->offset($request->start);
			$query->orderBy($sortingColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
			$isFilterApply = 0;

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
			// print_r(DB::getQueryLog());
			// die;

			$data = json_decode(json_encode($data), true);
			if ($isFilterApply == 1) {
				$recordsFiltered = count($data);
			}

			foreach ($data as $key => $value) {

				$data[$key]['product_brand'] = "<p>" . $value['product_brand_name'] . "</p>";
				$data[$key]['product_code'] = "<p>" . $value['product_code_name'] . "</p>";
				$data[$key]['description'] = "<p>" . $value['description'] . "</p>";
				$data[$key]['new_discount_percentage'] = "<input type='number'   min='0' max='100' step='1' class='form-control valid-discount' id='" . $value['id'] . "'  />";
			}

			$jsonData = array(
				"draw" => intval($request['draw']),
				// for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
				"recordsTotal" => intval($recordsTotal),
				// total number of records
				"recordsFiltered" => intval($recordsFiltered),
				// total number of records after searching, if there is no searching then totalFiltered = totalData
				"data" => $data, // total data array

			);
			return $jsonData;
		} else {
			$jsonData = array(
				"draw" => 0,
				// for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
				"recordsTotal" => 0,
				// total number of records
				"recordsFiltered" => 0,
				// total number of records after searching, if there is no searching then totalFiltered = totalData
				"data" => array(), // total data array

			);
			return $jsonData;
		}
	}
	function searchProductGroup(Request $request)
	{

		$ProductGroup = ProductGroup::select('id', 'name as text');
		$q = $request->q;
		$ProductGroup->where(function ($query) use ($q) {
			$query->where('name', 'like', '%' . $q . '%');
		});
		$ProductGroup->limit(5);
		$data = $ProductGroup->get();
		$data = json_decode(json_encode($data), true);
		$dataC = count($data);
		$data[$dataC]['id'] = 0;
		$data[$dataC]['text'] = "All";

		$response = array();
		$response['results'] = $data;
		$response['pagination']['more'] = false;
		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function save(Request $request)
	{

		$isSalePerson = isSalePerson();

		$isTaleSalesUser = isTaleSalesUser();
		if ($isTaleSalesUser == 1) {
			$TeleSalesCity = TeleSalesCity(Auth::user()->id);
		}

		if ($isTaleSalesUser == 1 && !in_array($request->user_city_id, $TeleSalesCity)) {
			$response = errorRes("Invalid access");
		}

		$channel_partner_type_id = [104, 105];

		// if ($request->user_type == 104 || $request->user_type == 105 && $isSalePerson == 1) {
		if (!in_array($request->user_type, $channel_partner_type_id) && $isSalePerson == 1) {
			$response = errorRes($request->user_type);
		} else {

			$user_address_line2 = isset($request->user_address_line2) ? $request->user_address_line2 : '';
			$channel_partner_d_address_line1 = isset($request->channel_partner_d_address_line1) ? $request->channel_partner_d_address_line1 : '';
			$channel_partner_d_address_line2 = isset($request->channel_partner_d_address_line2) ? $request->channel_partner_d_address_line2 : '';
			$channel_partner_d_pincode = isset($request->channel_partner_d_pincode) ? $request->channel_partner_d_pincode : '';
			$channel_partner_credit_days = isset($request->channel_partner_credit_days) ? $request->channel_partner_credit_days : 0;
			$channel_partner_credit_limit = isset($request->channel_partner_credit_limit) ? $request->channel_partner_credit_limit : 0;
			$channel_partner_payment_mode = isset($request->channel_partner_payment_mode) ? $request->channel_partner_payment_mode : 0;

			$data_verified = isset($request->channel_partner_data_verified) ? $request->channel_partner_data_verified : 0;
			$data_not_verified = isset($request->channel_partner_data_not_verified) ? $request->channel_partner_data_not_verified : 0;
			$missing_data = isset($request->channel_partner_missing_data) ? $request->channel_partner_missing_data : 0;
			$tele_verified = isset($request->channel_partner_tele_verified) ? $request->channel_partner_tele_verified : 0;
			$tele_not_verified = isset($request->channel_partner_tele_not_verified) ? $request->channel_partner_tele_not_verified : 0;

			$dataVerifiedStatus = 0;
			if ($data_verified == 1) {
				$dataVerifiedStatus = 1;
			} else if ($data_not_verified == 1) {
				$dataVerifiedStatus = 2;
			} else if ($missing_data == 1) {
				$dataVerifiedStatus = 3;
			}

			$rules = array();
			$rules['user_id'] = 'required';
			$rules['user_type'] = 'required';
			$rules['user_first_name'] = 'required';
			$rules['user_last_name'] = 'required';
			$rules['user_email'] = 'required|email:rfc,dns';
			$rules['user_phone_number'] = 'required|digits:10|regex:/^[1-9][0-9]*$/';
			$rules['user_address_line1'] = 'required';
			$rules['user_pincode'] = 'required';
			$rules['user_country_id'] = 'required';
			$rules['user_state_id'] = 'required';
			$rules['user_city_id'] = 'required';
			$rules['channel_partner_firm_name'] = 'required';
			$rules['channel_partner_reporting_manager'] = 'required';
			$rules['channel_partner_payment_mode'] = 'required';
			$rules['channel_partner_shipping_limit'] = 'required';
			$rules['channel_partner_shipping_cost'] = 'required';
			$rules['channel_partner_d_country_id'] = 'required';
			$rules['channel_partner_d_state_id'] = 'required';
			$rules['channel_partner_d_city_id'] = 'required';
			$rules['channel_partner_d_pincode'] = 'required';
			$rules['channel_partner_d_address_line1'] = 'required';

			if ($request->user_type != 104) {
				$rules['channel_partner_gst_number'] = 'required';
			}

			if ($isSalePerson == 0) {
				//$rules['channel_partner_sale_persons'] = 'required';
			}

			$customMessage = array(
				'user_id.required' => "Invalid parameters",
				'user_type.required' => "Invalid type",
				'user_first_name.required' => "Please enter first name",
				'user_last_name.required' => "Please enter last name",
				'user_email.required' => "Please enter email",
				'user_phone_number.required' => "Please enter phone number",
				'user_address_line1.required' => "Please enter addreessline1",
				'user_pincode.required' => "Please enter pincode",
				'user_country_id.required' => "Please select country",
				'user_state_id.is_required' => "Please select state",
				'user_city_id.required' => "Please select city",
				'channel_partner_firm_name.required' => "Please select firm name",
				'channel_partner_reporting_manager.required' => "Please select reporting manager",
				'channel_partner_payment_mode.required' => "Please select payment type",
				'channel_partner_shipping_limit.required' => "Please enter shipping limit",
				'channel_partner_shipping_cost.required' => "Please enter shipping cost",
				'channel_partner_d_country_id.required' => "Please select delivery country",
				'channel_partner_d_state_id.required' => "Please select delivery state",
				'channel_partner_d_city_id.required' => "Please select delivery city",
				'channel_partner_d_pincode.required' => "Please enter delivery pincode",
				'channel_partner_d_address_line1.required' => "Please enter delivery addreessline1",
				'channel_partner_gst_number.required' => "Please enter delivery GST number",
				'channel_partner_sale_persons.required' => "Please select sale persons",
			);

			$validator = Validator::make($request->all(), $rules, $customMessage);

			if ($validator->fails()) {

				$response = array();
				$response['status'] = 0;
				$response['msg'] = "The request could not be understood by the server due to malformed syntax";
				$response['statuscode'] = 400;
				$response['data'] = $validator->errors();
			} else {

				//if ($isSalePerson == 0) {
				if (isset($request->channel_partner_sale_persons)) {
					$channel_partner_sale_persons = implode(",", $request->channel_partner_sale_persons);
				} else {
					$channel_partner_sale_persons = "";
				}

				// } else {

				// 	$channel_partner_sale_persons = Auth::user()->id;

				// }

				$phone_number = $request->user_phone_number;
				$channel_partner_gst_number = "";
				if (isset($request->channel_partner_gst_number) && $request->channel_partner_gst_number != "") {
					$channel_partner_gst_number = $request->channel_partner_gst_number;
				}

				$alreadyEmail = User::query();
				$alreadyEmail->where('email', $request->user_email);
				$alreadyEmail->where('type', '!=', 10000);

				if ($request->user_id != 0) {
					$alreadyEmail->where('id', '!=', $request->user_id);
				}
				$alreadyEmail = $alreadyEmail->first();

				$alreadyPhoneNumber = User::query();
				$alreadyPhoneNumber->where('phone_number', $request->user_phone_number);
				$alreadyPhoneNumber->where('type', '!=', 10000);

				if ($request->user_id != 0) {
					$alreadyPhoneNumber->where('id', '!=', $request->user_id);
				}
				$alreadyPhoneNumber = $alreadyPhoneNumber->first();

				$AllUserTypes = getAllUserTypes();

				if ($alreadyEmail) {

					$response = errorRes("Email already exists(" . $AllUserTypes[$alreadyEmail->type]['name'] . "), Try with another email");
				} else if ($alreadyPhoneNumber) {
					$response = errorRes("Phone number already exists(" . $AllUserTypes[$alreadyPhoneNumber->type]['name'] . "), Try with another phone number");
				} else {
					$user_address_line1 = isset($request->user_address_line1) ? $request->user_address_line1 : '';
					$user_address_line2 = isset($request->user_address_line2) ? $request->user_address_line2 : '';
					$user_pincode = isset($request->user_pincode) ? $request->user_pincode : '';
	
					$channel_partner_reporting = explode("-", $request->channel_partner_reporting_manager);

					if ($channel_partner_reporting[0] == "c") {

						$user_company_id = $channel_partner_reporting[1];
						$reporting_manager_id = 0;
					} else {

						$ChannelPartner = ChannelPartner::select('user_id', 'reporting_company_id')->where('user_id', $channel_partner_reporting[1])->first();
						$user_company_id = $ChannelPartner->reporting_company_id;
						$reporting_manager_id = $ChannelPartner->user_id;
					}

					$isCreditUpdate = 0;

					$previousStatus = 1;
					$paymentModeChannelPartner = 0;
					$isSalesUserSame = 0;

					

					if ($request->user_id == 0) {


						$User = User::where('type', 10000)->where(function ($query) use ($request) {
							$query->where('email', $request->user_email)->orWhere('phone_number', $request->user_phone_number);
						})->first();

						if ($User) {
							$User->type = $request->user_type;
							$User->reference_type = getChannelPartners()[$User->type]['lable'];
							$User->reference_id = 0;
						} else {


							$User = new User();
							$User->created_by = Auth::user()->id;
							$User->password = Hash::make("111111");
							$User->last_active_date_time = date('Y-m-d H:i:s');
							$User->last_login_date_time = date('Y-m-d H:i:s');
							$User->avatar = "default.png";
						}

						$ChannelPartner = new ChannelPartner();
						$ChannelPartner->credit_limit = $channel_partner_credit_limit;
						$ChannelPartner->pending_credit = $channel_partner_credit_limit;
						$isCreditUpdate = 1;
					} else {
						$User = User::find($request->user_id);
						$previousStatus = $User->status;

						$ChannelPartner = ChannelPartner::find($User->reference_id);
						if (!$ChannelPartner) {

							$ChannelPartner = new ChannelPartner();
							$ChannelPartner->credit_limit = $channel_partner_credit_limit;
							$ChannelPartner->pending_credit = $channel_partner_credit_limit;
							$isCreditUpdate = 1;
						} else {

							$paymentModeChannelPartner = $ChannelPartner->payment_mode;

							if ($ChannelPartner->payment_mode != 2 && $request->channel_partner_payment_mode == 2) {

								$ChannelPartner->credit_limit = $channel_partner_credit_limit;
								$ChannelPartner->pending_credit = $channel_partner_credit_limit;
								$isCreditUpdate = 1;
							}
						}
					}

					$log_data = [];

					if ($User->first_name != $request->user_first_name) {
						$new_value = $request->user_first_name;
						$old_value = $User->first_name;
						$change_field = "User First Name Change : " . $old_value . " To " . $new_value;

						$log_value = [];
						$log_value['field_name'] = "user_first_name";
						$log_value['new_value'] = $new_value;
						$log_value['old_value'] = $old_value;
						$log_value['description'] =  $change_field;

						array_push($log_data, $log_value);
					}

					if ($User->last_name != $request->user_last_name) {
						$new_value = $request->user_last_name;
						$old_value = $User->last_name;
						$change_field = "User Last Name Change : " . $old_value . " To " . $new_value;

						$log_value = [];
						$log_value['field_name'] = "user_last_name";
						$log_value['new_value'] = $new_value;
						$log_value['old_value'] = $old_value;
						$log_value['description'] =  $change_field;

						array_push($log_data, $log_value);
					}

					if ($User->email != $request->user_email) {
						$new_value = $request->user_email;
						$old_value = $User->email;
						$change_field = "User Last Name Change : " . $old_value . " To " . $new_value;

						$log_value = [];
						$log_value['field_name'] = "user_email";
						$log_value['new_value'] = $new_value;
						$log_value['old_value'] = $old_value;
						$log_value['description'] =  $change_field;

						array_push($log_data, $log_value);
					}

					if ($User->phone_number != $request->user_phone_number) {
						$new_value = $request->user_phone_number;
						$old_value = $User->phone_number;
						$change_field = "User Last Name Change : " . $old_value . " To " . $new_value;

						$log_value = [];
						$log_value['field_name'] = "user_phone_number";
						$log_value['new_value'] = $new_value;
						$log_value['old_value'] = $old_value;
						$log_value['description'] =  $change_field;

						array_push($log_data, $log_value);
					}

					if ($User->address_line1 != $user_address_line1) {
						$new_value = $user_address_line1;
						$old_value = $User->address_line1;
						$change_field = "User Address Line 1 Change : " . $old_value . " To " . $new_value;

						$log_value = [];
						$log_value['field_name'] = "user_address_line1";
						$log_value['new_value'] = $new_value;
						$log_value['old_value'] = $old_value;
						$log_value['description'] =  $change_field;

						array_push($log_data, $log_value);
					}

					if ($User->address_line2 != $user_address_line2) {
						$new_value = $user_address_line2;
						$old_value = $User->address_line2;
						$change_field = "User Address Line 2 Change : " . $old_value . " To " . $new_value;

						$log_value = [];
						$log_value['field_name'] = "user_address_line2";
						$log_value['new_value'] = $new_value;
						$log_value['old_value'] = $old_value;
						$log_value['description'] =  $change_field;

						array_push($log_data, $log_value);
					}

					if ($User->status != $request->user_status) {
						$statusMap = [
							0 => "Inactive",
							1 => "Active",
							2 => "Pending"
						];
					
						$new_status = $statusMap[$request->user_status] ?? "Unknown";
						$old_status = $statusMap[$User->status] ?? "Unknown";
						$change_field = "User Status Change: " . $old_status . " To " . $new_status;
					
						$log_value = [
							'field_name' => "user_status",
							'new_value' => $new_status,
							'old_value' => $old_status,
							'description' => $change_field
						];
					
						array_push($log_data, $log_value);
					}

					if ($User->pincode != $user_pincode) {
						$new_value = $user_pincode;
						$old_value = $User->pincode;
						$change_field = "User Pincode Change : " . $old_value . " To " . $new_value;

						$log_value = [];
						$log_value['field_name'] = "user_pincode";
						$log_value['new_value'] = $new_value;
						$log_value['old_value'] = $old_value;
						$log_value['description'] =  $change_field;

						array_push($log_data, $log_value);
					}

					if ($User->city_id != $request->user_city_id) {
						$new_value = $request->user_city_id;
						$old_value = $User->city_id;
						
						$old_city = CityList::find($old_value);
						$new_city = CityList::find($new_value);
						
						// Check if the city with the old value exists
						$old_city_name = $old_city ? $old_city->name : 'Unknown';
						
						// Check if the city with the new value exists
						$new_city_name = $new_city ? $new_city->name : 'Unknown';
						
						$change_field = "User City Change : " . $old_city_name . " To " . $new_city_name;
					
						$log_value = [];
						$log_value['field_name'] = "user_city";
						$log_value['new_value'] = $new_value;
						$log_value['old_value'] = $old_value;
						$log_value['description'] = $change_field;
					
						array_push($log_data, $log_value);
					}

					if ($User->state_id != $request->user_state_id) {
						$new_value = $request->user_state_id;
						$old_value = $User->state_id;
						
						$old_state = StateList::find($old_value);
						$new_state = StateList::find($new_value);
						
						// Check if the state with the old value exists
						$old_state_name = $old_state ? $old_state->name : 'Unknown';
						
						// Check if the state with the new value exists
						$new_state_name = $new_state ? $new_state->name : 'Unknown';
						
						$change_field = "User State Change : " . $old_state_name . " To " . $new_state_name;

						$log_value = [];
						$log_value['field_name'] = "user_state";
						$log_value['new_value'] = $new_value;
						$log_value['old_value'] = $old_value;
						$log_value['description'] = $change_field;
						array_push($log_data, $log_value);
					}

					if ($User->country_id != $request->user_country_id) {
						$new_value = $request->user_country_id;
						$old_value = $User->country_id;
						
						$old_country = CountryList::find($old_value);
						$new_country = CountryList::find($new_value);
						
						// Check if the country with the old value exists
						$old_country_name = $old_country ? $old_country->name : 'Unknown';
						
						// Check if the country with the new value exists
						$new_country_name = $new_country ? $new_country->name : 'Unknown';
						
						$change_field = "User Country Change : " . $old_country_name . " To " . $new_country_name;

						$log_value = [];
						$log_value['field_name'] = "user_country";
						$log_value['new_value'] = $new_value;
						$log_value['old_value'] = $old_value;
						$log_value['description'] = $change_field;

						array_push($log_data, $log_value);
					}

					if ($ChannelPartner->firm_name != $request->channel_partner_firm_name) {
						$new_value = $request->channel_partner_firm_name;
						$old_value = $ChannelPartner->firm_name;
						$change_field = "User Firm Change : " . $old_value . " To " . $new_value;

						$log_value = [];
						$log_value['field_name'] = "channel_partner_firm_name";
						$log_value['new_value'] = $new_value;
						$log_value['old_value'] = $old_value;
						$log_value['description'] =  $change_field;

						array_push($log_data, $log_value);
					}

					if ($ChannelPartner->gst_number != $request->channel_partner_gst_number) {
						$new_value = $request->channel_partner_gst_number;
						$old_value = $ChannelPartner->gst_number;
						$change_field = "User GST Number Change : " . $old_value . " To " . $new_value;

						$log_value = [];
						$log_value['field_name'] = "channel_partner_gst_number";
						$log_value['new_value'] = $new_value;
						$log_value['old_value'] = $old_value;
						$log_value['description'] =  $change_field;

						array_push($log_data, $log_value);
					}

					if ($ChannelPartner->credit_days != $request->channel_partner_credit_days) {
						$new_value = $request->channel_partner_credit_days;
						$old_value = $ChannelPartner->credit_days;
						$change_field = "User Credit Day Change : " . $old_value . " To " . $new_value;

						$log_value = [];
						$log_value['field_name'] = "channel_partner_credit_days";
						$log_value['new_value'] = $new_value;
						$log_value['old_value'] = $old_value;
						$log_value['description'] =  $change_field;

						array_push($log_data, $log_value);
					}

					if ($ChannelPartner->shipping_limit != $request->channel_partner_shipping_limit) {
						$new_value = $request->channel_partner_shipping_limit;
						$old_value = $ChannelPartner->shipping_limit;
						$change_field = "User Shipping Limit Change : " . $old_value . " To " . $new_value;

						$log_value = [];
						$log_value['field_name'] = "channel_partner_shipping_limit";
						$log_value['new_value'] = $new_value;
						$log_value['old_value'] = $old_value;
						$log_value['description'] =  $change_field;

						array_push($log_data, $log_value);
					}

					if ($ChannelPartner->shipping_cost != $request->channel_partner_shipping_cost) {
						$new_value = $request->channel_partner_shipping_cost;
						$old_value = $ChannelPartner->shipping_cost;
						$change_field = "User Shipping Cost Change : " . $old_value . " To " . $new_value;

						$log_value = [];
						$log_value['field_name'] = "channel_partner_shipping_cost";
						$log_value['new_value'] = $new_value;
						$log_value['old_value'] = $old_value;
						$log_value['description'] =  $change_field;

						array_push($log_data, $log_value);
					}

					if ($ChannelPartner->d_address_line1 != $channel_partner_d_address_line1) {
						$new_value = $channel_partner_d_address_line1;
						$old_value = $ChannelPartner->d_address_line1;
						$change_field = "ChannelPartner Address Line 1 Change : " . $old_value . " To " . $new_value;

						$log_value = [];
						$log_value['field_name'] = "channel_partner_d_address_line1";
						$log_value['new_value'] = $new_value;
						$log_value['old_value'] = $old_value;
						$log_value['description'] =  $change_field;

						array_push($log_data, $log_value);
					}

					if ($ChannelPartner->d_address_line2 != $channel_partner_d_address_line2) {
						$new_value = $channel_partner_d_address_line2;
						$old_value = $ChannelPartner->d_address_line2;
						$change_field = "ChannelPartner Address Line 2 Change : " . $old_value . " To " . $new_value;

						$log_value = [];
						$log_value['field_name'] = "channel_partner_d_address_line2";
						$log_value['new_value'] = $new_value;
						$log_value['old_value'] = $old_value;
						$log_value['description'] =  $change_field;

						array_push($log_data, $log_value);
					}

					if ($ChannelPartner->d_pincode != $channel_partner_d_pincode) {
						$new_value = $channel_partner_d_pincode;
						$old_value = $ChannelPartner->d_pincode;
						$change_field = "ChannelPartner Pincode Change : " . $old_value . " To " . $new_value;

						$log_value = [];
						$log_value['field_name'] = "channel_partner_d_pincode";
						$log_value['new_value'] = $new_value;
						$log_value['old_value'] = $old_value;
						$log_value['description'] =  $change_field;

						array_push($log_data, $log_value);
					}

					if ($ChannelPartner->d_city_id != $request->channel_partner_d_city_id) {
						$new_value = $request->channel_partner_d_city_id;
						$old_value = $ChannelPartner->d_city_id;
						
						$old_city = CityList::find($old_value);
						$new_city = CityList::find($new_value);
						
						// Check if the city with the old value exists
						$old_city_name = $old_city ? $old_city->name : 'Unknown';
						
						// Check if the city with the new value exists
						$new_city_name = $new_city ? $new_city->name : 'Unknown';
						
						$change_field = "ChannelPartner City Change : " . $old_city_name . " To " . $new_city_name;
					
						$log_value = [];
						$log_value['field_name'] = "user_city";
						$log_value['new_value'] = $new_value;
						$log_value['old_value'] = $old_value;
						$log_value['description'] = $change_field;
					
						array_push($log_data, $log_value);
					}

					if ($ChannelPartner->d_state_id != $request->channel_partner_d_state_id) {
						$new_value = $request->channel_partner_d_state_id;
						$old_value = $ChannelPartner->d_state_id;
						
						$old_state = StateList::find($old_value);
						$new_state = StateList::find($new_value);
						
						// Check if the state with the old value exists
						$old_state_name = $old_state ? $old_state->name : 'Unknown';
						
						// Check if the state with the new value exists
						$new_state_name = $new_state ? $new_state->name : 'Unknown';
						
						$change_field = "ChannelPartner State Change : " . $old_state_name . " To " . $new_state_name;

						$log_value = [];
						$log_value['field_name'] = "user_state";
						$log_value['new_value'] = $new_value;
						$log_value['old_value'] = $old_value;
						$log_value['description'] = $change_field;
						array_push($log_data, $log_value);
					}

					if ($ChannelPartner->d_country_id != $request->channel_partner_d_country_id) {
						$new_value = $request->channel_partner_d_country_id;
						$old_value = $ChannelPartner->d_country_id;
						
						$old_country = CountryList::find($old_value);
						$new_country = CountryList::find($new_value);
						
						// Check if the country with the old value exists
						$old_country_name = $old_country ? $old_country->name : 'Unknown';
						
						// Check if the country with the new value exists
						$new_country_name = $new_country ? $new_country->name : 'Unknown';
						
						$change_field = "ChannelPartner Country Change : " . $old_country_name . " To " . $new_country_name;

						$log_value = [];
						$log_value['field_name'] = "user_country";
						$log_value['new_value'] = $new_value;
						$log_value['old_value'] = $old_value;
						$log_value['description'] = $change_field;

						array_push($log_data, $log_value);
					}

					if ($ChannelPartner->payment_mode != $request->channel_partner_payment_mode) {
						$paymentModeMap = [
							0 => "PDC",
							1 => "ADVANCE",
							2 => "CREDIT"
						];
					
						$new_payment_mode = $paymentModeMap[$request->$channel_partner_payment_mode] ?? "Unknown";
						$old_payment_mode = $paymentModeMap[$ChannelPartner->payment_mode] ?? "Unknown";
						$change_field = "ChannelPartner Payment Mode Change: " . $old_payment_mode . " To " . $new_payment_mode;
					
						$log_value = [];
						$log_value['field_name'] = "channel_partner_payment_mode";
						$log_value['new_value'] = $new_payment_mode;
						$log_value['old_value'] = $old_payment_mode;
						$log_value['description'] = $change_field;
						
					
						array_push($log_data, $log_value);
					}

					if ($ChannelPartner->sale_persons != $request->channel_partner_payment_mode) {
						$new_sale_persons_ids = is_array($request->channel_partner_sale_persons) ? $request->channel_partner_sale_persons : [];
						$new_value = implode(",", $new_sale_persons_ids);
						$old_value = $ChannelPartner->sale_persons;
					
						// Check if the sales persons have actually changed
						if ($new_value !== $old_value) {
							$old_user = User::selectRaw(DB::raw('CONCAT(first_name," ", last_name) AS text'))->where('id', $old_value)->first();
							$old_text = $old_user ? $old_user->text : 'Unknown';
					
							$new_users = User::selectRaw(DB::raw('CONCAT(first_name," ", last_name) AS text'))->whereIn('id', $new_sale_persons_ids)->get();
							$new_text = $new_users->isNotEmpty() ? $new_users->pluck('text')->implode(', ') : 'Unknown';
					
							$change_field = "User Sale Person Change: " . $old_text . " To " . $new_text;
					
							$log_value = [];
							$log_value['field_name'] = "sale_person";
							$log_value['new_value'] = $new_value;
							$log_value['old_value'] = $old_value;
							$log_value['description'] = $change_field;
					
							// Add log value to log data
							array_push($log_data, $log_value);
						}
					}
					
					if ($ChannelPartner->reporting_manager_id != $reporting_manager_id) {

						$old_manager = User::select('first_name', 'type')->where('id', $ChannelPartner->reporting_manager_id)->first();
						$new_manager = User::select('first_name', 'type')->where('id', $reporting_manager_id)->first();
					
						$old_manager_name = $old_manager ? $old_manager->first_name : 'Unknown';
						$old_manager_type = $old_manager ? "(" . getUserTypeName($old_manager->type) . ")" : '';
					
						$new_manager_name = $new_manager ? $new_manager->first_name : 'Unknown';
						$new_manager_type = $new_manager ? "(" . getUserTypeName($new_manager->type) . ")" : '';
					
						$change_field = "Channel Partner Reporting Manager Change: $old_manager_name $old_manager_type To $new_manager_name $new_manager_type";
					
						// Prepare log value
						$log_value = [];
						$log_value['field_name'] = "reporting_manager_id";
						$log_value['new_value'] = $reporting_manager_id;
						$log_value['old_value'] = $ChannelPartner->reporting_manager_id;
						$log_value['description'] = $change_field;
					
						// Add log value to log data
						array_push($log_data, $log_value);
					}
					
					
					foreach ($log_data as $log_value) {
						$user_log = new UserLog();
						$user_log->user_id = Auth::user()->id;
						$user_log->log_type = "CHANNEL-PARTNER-LOG";
						$user_log->field_name = $log_value['field_name'];
						$user_log->old_value = $log_value['old_value'];
						$user_log->new_value = $log_value['new_value'];
						$user_log->reference_type = "Channel Partner";
						$user_log->reference_id = $request->user_id;
						$user_log->transaction_type = "Channel Partner Edit";
						$user_log->description = $log_value['description'];
						$user_log->source = "WEB";
						$user_log->entryby = Auth::user()->id;
						$user_log->entryip = $request->ip();
						$user_log->save();
					}

					$User->first_name = $request->user_first_name;
					$User->last_name = $request->user_last_name;
					$User->email = $request->user_email;
					$User->dialing_code = "+91";
					$User->phone_number = $request->user_phone_number;
					$User->ctc = 0;
					$User->address_line1 = $request->user_address_line1;
					$User->address_line2 = $user_address_line2;
					$User->pincode = $request->user_pincode;
					$User->country_id = $request->user_country_id;
					$User->state_id = $request->user_state_id;
					$User->city_id = $request->user_city_id;
					$User->company_id = $user_company_id;
					$User->type = $request->user_type;
					if ($isSalePerson == 0) {
						$User->status = $request->user_status;
					}

					$User->reference_type = 0;
					$User->reference_id = 0;
					$User->save();

					$ChannelPartner->data_verified_status = $dataVerifiedStatus;
					$ChannelPartner->user_id = $User->id;
					$ChannelPartner->type = $request->user_type;
					$ChannelPartner->firm_name = $request->channel_partner_firm_name;
					$ChannelPartner->reporting_manager_id = $reporting_manager_id;
					$ChannelPartner->reporting_company_id = $user_company_id;

					if ($isSalePerson == 1) {

						if ($request->user_id == 0) {
							if (strpos($channel_partner_sale_persons, $ChannelPartner->sale_persons) === false) {
								$isSalesUserSame = 1;
							}
							$ChannelPartner->sale_persons = $channel_partner_sale_persons;
						}
					} else {
						if (strpos($channel_partner_sale_persons, $ChannelPartner->sale_persons) === false) {
							$isSalesUserSame = 1;
						}
						$ChannelPartner->sale_persons = $channel_partner_sale_persons;
					}

					$ChannelPartner->payment_mode = $request->channel_partner_payment_mode;
					$ChannelPartner->credit_days = $channel_partner_credit_days;
					$ChannelPartner->gst_number = $channel_partner_gst_number;
					$ChannelPartner->shipping_limit = $request->channel_partner_shipping_limit;
					$ChannelPartner->shipping_cost = $request->channel_partner_shipping_cost;
					$ChannelPartner->d_address_line1 = $request->channel_partner_d_address_line1;
					$ChannelPartner->d_address_line2 = $channel_partner_d_address_line2;
					$ChannelPartner->d_pincode = $request->channel_partner_d_pincode;
					$ChannelPartner->d_country_id = $request->channel_partner_d_country_id;
					$ChannelPartner->d_state_id = $request->channel_partner_d_state_id;
					$ChannelPartner->d_city_id = $request->channel_partner_d_city_id;

					$ChannelPartner->data_verified = $data_verified;
					$ChannelPartner->tele_verified = $tele_verified;
					$ChannelPartner->tele_not_verified = $tele_not_verified;
					$ChannelPartner->data_not_verified = $data_not_verified;
					$ChannelPartner->missing_data = $missing_data;
					$ChannelPartner->save();

					$User->reference_type = getChannelPartners()[$User->type]['lable'];
					$User->reference_id = $ChannelPartner->id;
					if ($isSalePerson == 1 && $request->user_id == 0) {
						$User->status = 2;
					}
					$User->save();
					$currentStatus = $User->status;

					//
					if ($isCreditUpdate == 1) {

						$CreditTranscationLog = new CreditTranscationLog();
						$CreditTranscationLog->user_id = $User->id;
						$CreditTranscationLog->type = 1;
						$CreditTranscationLog->request_amount = $channel_partner_credit_limit;
						$CreditTranscationLog->amount = $channel_partner_credit_limit;
						$CreditTranscationLog->description = "intial";
						$CreditTranscationLog->save();
					}

					$debugLog = [];
					if ($request->user_id != 0) {
						$debugLog['name'] = "channel-partner-add";
						$debugLog['description'] = "channelpartner #" . $User->id . "(" . $User->first_name . " " . $User->last_name . ") has been updated ";
						$response = successRes("Successfully saved channel partner");
						// saveDebugLog($debugLog);

						// if ($ChannelPartner->payment_mode != $paymentModeChannelPartner) {
						if ($ChannelPartner->payment_mode != $request->channel_partner_payment_mode) {
							//ADVANCE

							if ($ChannelPartner->payment_mode == 1) {




								$params = array();
								// $params['bcc_email'] = "ankitsardhara4@gmail.com";
								// $params['to_email'] = "ankitsardhara4@gmail.com";

								$params['to_email'] = $User->email;
								$Parameter = Parameter::where('code', 'channel-partner-deactivate-email')->first();
								$channelPartnerDeactiveMail = $Parameter->name_value;
								$bccEmails = array("nirav@whitelion.in", "jenish@whitelion.in", "vishal@whitelion.in", "marketing@whitelion.in");
								if ($channelPartnerDeactiveMail != "") {
									$channelPartnerDeactiveMail = explode(",", $channelPartnerDeactiveMail);
									foreach ($channelPartnerDeactiveMail as $keyM => $valueM) {

										if ($valueM != "") {
											$bccEmails[] = $valueM;
										}
									}
								}

								$salePersons = $ChannelPartner->sale_persons;

								$bccEmailUserIds = array();
								if ($salePersons != "") {
									$salePersons = explode(",", $salePersons);
									foreach ($salePersons as $keyS => $valueS) {

										$salesParentsIds = getParentSalePersonsIds($valueS);
										$bccEmailUserIds = array_merge($bccEmailUserIds, $salesParentsIds);
									}
								}

								$bccEmailUserIds = array_unique($bccEmailUserIds);
								$bccEmailUserIds = array_values($bccEmailUserIds);

								$bccEmailUser = User::select('email')->where('id', $bccEmailUserIds)->get();
								foreach ($bccEmailUser as $keyBE => $valueBE) {

									$bccEmails[] = $valueBE->email;
								}

								$accountAndDispatchUsers = User::select('email')->where('parent_id', $ChannelPartner->reporting_manager_id)->where('type', array(3, 4))->get();

								foreach ($accountAndDispatchUsers as $keyBE => $valueBE) {
									$bccEmails[] = $valueBE->email;
								}
								
								$configrationForNotify = configrationForNotify();

								// $params['bcc_email'] = $bccEmails;
								// $params['firm_name'] = $ChannelPartner->firm_name;
								// $params['first_name'] = $User->first_name;
								// $params['last_name'] = $User->last_name;
								// $params['city_name'] = getCityName($ChannelPartner->d_city_id);
								// $params['channel_partner_type'] = getChannelPartners()[$User->type]['short_name'];


								// $params['from_name'] = $configrationForNotify['from_name'];
								// $params['from_email'] = $configrationForNotify['from_email'];
								// $params['to_name'] = $configrationForNotify['to_name'];
								// $params['subject'] = "Payment Terms Change";



								// if (Config::get('app.env') == "local") {
								// 	$params['to_email'] = $configrationForNotify['test_email'];
								// 	$params['bcc_email'] = $configrationForNotify['test_email_bcc'];
								// }



								// Mail::send('emails.channel_partner_advance', ['params' => $params], function ($m) use ($params) {
								// 	$m->from($params['from_email'], $params['from_name']);
								// 	$m->bcc($params['bcc_email']);
								// 	$m->to($params['to_email'], $params['to_name'])->subject($params['subject']);
								// });

								$Mailarr = [];
								$Mailarr['from_name'] = $configrationForNotify['from_name'];
								$Mailarr['from_email'] = $configrationForNotify['from_email'];
								$Mailarr['to_email'] = $User->email;
								$Mailarr['to_name'] = $configrationForNotify['to_name'];
								$Mailarr['bcc_email'] = implode(',', $bccEmails);
								$Mailarr['cc_email'] = "";
								$Mailarr['subject'] = 'Payment Terms Change';
								$Mailarr['transaction_id'] = $User->id;
								$Mailarr['transaction_name'] = "Channel Partner";
								$Mailarr['transaction_type'] = "Email";
								$Mailarr['transaction_detail'] = "emails.channel_partner_advance";
								$Mailarr['attachment'] = "";
								$Mailarr['remark'] = "Channel Partner Payment Terms Change";
								$Mailarr['source'] = "Web";
								$Mailarr['entryip'] = $request->ip();

								if (Config::get('app.env') == 'local') {
									$Mailarr['to_email'] = $configrationForNotify['test_email'];
									$Mailarr['bcc_email'] = implode(', ', $configrationForNotify['test_email_bcc']);
								}

								saveNotificationScheduler($Mailarr);

							} else if ($ChannelPartner->payment_mode == 2) {

								$params = array();
								// $params['bcc_email'] = "ankitsardhara4@gmail.com";
								// $params['to_email'] = "ankitsardhara4@gmail.com";

								$params['to_email'] = $User->email;
								$Parameter = Parameter::where('code', 'channel-partner-deactivate-email')->first();
								$channelPartnerDeactiveMail = $Parameter->name_value;
								$bccEmails = array("nirav@whitelion.in", "jenish@whitelion.in", "vishal@whitelion.in", "marketing@whitelion.in");
								if ($channelPartnerDeactiveMail != "") {
									$channelPartnerDeactiveMail = explode(",", $channelPartnerDeactiveMail);
									foreach ($channelPartnerDeactiveMail as $keyM => $valueM) {

										if ($valueM != "") {
											$bccEmails[] = $valueM;
										}
									}
								}

								$salePersons = $ChannelPartner->sale_persons;

								$bccEmailUserIds = array();
								if ($salePersons != "") {
									$salePersons = explode(",", $salePersons);
									foreach ($salePersons as $keyS => $valueS) {

										$salesParentsIds = getParentSalePersonsIds($valueS);
										$bccEmailUserIds = array_merge($bccEmailUserIds, $salesParentsIds);
									}
								}

								$bccEmailUserIds = array_unique($bccEmailUserIds);
								$bccEmailUserIds = array_values($bccEmailUserIds);

								$bccEmailUser = User::select('email')->where('id', $bccEmailUserIds)->get();
								foreach ($bccEmailUser as $keyBE => $valueBE) {

									$bccEmails[] = $valueBE->email;
								}

								$accountAndDispatchUsers = User::select('email')->where('parent_id', $ChannelPartner->reporting_manager_id)->where('type', array(3, 4))->get();

								foreach ($accountAndDispatchUsers as $keyBE => $valueBE) {
									$bccEmails[] = $valueBE->email;
								}
								//ADVANCE


								$configrationForNotify = configrationForNotify();
								// $params['bcc_email'] = $bccEmails;
								// $params['firm_name'] = $ChannelPartner->firm_name;
								// $params['first_name'] = $User->first_name;
								// $params['last_name'] = $User->last_name;



								// $params['from_name'] = $configrationForNotify['from_name'];
								// $params['from_email'] = $configrationForNotify['from_email'];
								// $params['to_name'] = $configrationForNotify['to_name'];
								// $params['subject'] = "Payment Terms Change";
								// $params['credit_limit'] = $channel_partner_credit_limit;
								// $params['credit_day'] = $channel_partner_credit_days;

								// if (Config::get('app.env') == "local") {

								// 	$params['to_email'] = $configrationForNotify['test_email'];
								// 	$params['bcc_email'] = $configrationForNotify['test_email_bcc'];
								// }

								// Mail::send('emails.channel_partner_credit', ['params' => $params], function ($m) use ($params) {
								// 	$m->from($params['from_email'], $params['from_name']);
								// 	$m->bcc($params['bcc_email']);
								// 	$m->to($params['to_email'], $params['to_name'])->subject($params['subject']);
								// });

								$Mailarr = [];
								$Mailarr['from_name'] = $configrationForNotify['from_name'];
								$Mailarr['from_email'] = $configrationForNotify['from_email'];
								$Mailarr['to_email'] = $User->email;
								$Mailarr['to_name'] = $configrationForNotify['to_name'];
								$Mailarr['bcc_email'] = implode(',', $bccEmails);
								$Mailarr['cc_email'] = "";
								$Mailarr['subject'] = 'Payment Terms Change';
								$Mailarr['transaction_id'] = $User->id;
								$Mailarr['transaction_name'] = "Channel Partner";
								$Mailarr['transaction_type'] = "Email";
								$Mailarr['transaction_detail'] = "emails.channel_partner_credit";
								$Mailarr['attachment'] = "";
								$Mailarr['remark'] = "Channel Partner Payment Terms Change";
								$Mailarr['source'] = "Web";
								$Mailarr['entryip'] = $request->ip();

								if (Config::get('app.env') == 'local') {
									$Mailarr['to_email'] = $configrationForNotify['test_email'];
									$Mailarr['bcc_email'] = implode(', ', $configrationForNotify['test_email_bcc']);
								}

								saveNotificationScheduler($Mailarr);
							}
						}
					} else {
					$mobileNotificationTitle = 'New Channel Partner Create';
                    $mobileNotificationMessage = 'New Channel Partner ' . $User->first_name . ' ' . $User->last_name . ' Added By ' . Auth::user()->first_name . ' ' . Auth::user()->last_name;
                    $notificationUserids = getParentSalePersonsIds($ChannelPartner->sale_persons);
                    $notificationUserids[] = $ChannelPartner->sale_persons;
                    $UsersNotificationTokens = UsersNotificationTokens($notificationUserids);
                    sendNotificationTOAndroid($mobileNotificationTitle, $mobileNotificationMessage, $UsersNotificationTokens, 'Channel Partner', $User);

                    $user_log = new UserLog();
                    $user_log->user_id = Auth::user()->id;
                    $user_log->log_type = "CHANNEL-PARTNER-LOG";
                    $user_log->field_name = '';
                    $user_log->old_value = '';
                    $user_log->new_value = '';
                    $user_log->reference_type = "Channel Partner";
                    $user_log->reference_id = $User->id;
                    $user_log->transaction_type = "Channel Partner Create";
                    $user_log->description = 'New Channel Partner Created';
                    $user_log->source = "WEB";
                    $user_log->entryby = Auth::user()->id;
                    $user_log->entryip = $request->ip();
                    $user_log->save();

                    $debugLog['name'] = 'channel-partner-add';
                    $debugLog['description'] = 'user #' . $User->id . '(' . $User->first_name . ' ' . $User->last_name . ') has been added ';
                    $response = successRes('Successfully added user');
                    $response['user_id'] = $User->id;
                
					}
					saveDebugLog($debugLog);

					if ($previousStatus == 1 && $currentStatus == 0) {

						$params = array();
						// $params['bcc_email'] = "ankitsardhara4@gmail.com";
						// $params['to_email'] = "ankitsardhara4@gmail.com";

						$params['to_email'] = $User->email;
						$Parameter = Parameter::where('code', 'channel-partner-deactivate-email')->first();
						$channelPartnerDeactiveMail = $Parameter->name_value;
						$bccEmails = array("nirav@whitelion.in", "jenish@whitelion.in", "vishal@whitelion.in", "marketing@whitelion.in");
						if ($channelPartnerDeactiveMail != "") {
							$channelPartnerDeactiveMail = explode(",", $channelPartnerDeactiveMail);
							foreach ($channelPartnerDeactiveMail as $keyM => $valueM) {

								if ($valueM != "") {
									$bccEmails[] = $valueM;
								}
							}
						}

						$salePersons = $ChannelPartner->sale_persons;

						$bccEmailUserIds = array();
						if ($salePersons != "") {
							$salePersons = explode(",", $salePersons);
							foreach ($salePersons as $keyS => $valueS) {

								$salesParentsIds = getParentSalePersonsIds($valueS);
								$bccEmailUserIds = array_merge($bccEmailUserIds, $salesParentsIds);
							}
						}

						$bccEmailUserIds = array_unique($bccEmailUserIds);
						$bccEmailUserIds = array_values($bccEmailUserIds);

						$bccEmailUser = User::select('email')->where('id', $bccEmailUserIds)->get();
						foreach ($bccEmailUser as $keyBE => $valueBE) {

							$bccEmails[] = $valueBE->email;
						}

						$accountAndDispatchUsers = User::select('email')->where('parent_id', $ChannelPartner->reporting_manager_id)->where('type', array(3, 4))->get();

						foreach ($accountAndDispatchUsers as $keyBE => $valueBE) {
							$bccEmails[] = $valueBE->email;
						}

						$configrationForNotify = configrationForNotify();

						// $params['bcc_email'] = $bccEmails;
						// $params['firm_name'] = $ChannelPartner->firm_name;
						// $params['first_name'] = $User->first_name;
						// $params['last_name'] = $User->last_name;
						// $params['city_name'] = getCityName($ChannelPartner->d_city_id);
						// $params['channel_partner_type'] = getChannelPartners()[$User->type]['short_name'];
						// $params['from_name'] = $configrationForNotify['from_name'];
						// $params['from_email'] = $configrationForNotify['from_email'];
						// $params['to_name'] = $configrationForNotify['to_name'];
						// $params['subject'] = "Updates: Channel Partner's account deactivated.";

						// if (Config::get('app.env') == "local") {
// 
							// $params['to_email'] = $configrationForNotify['test_email'];
							// $params['bcc_email'] = $configrationForNotify['test_email_bcc'];
						// }

						// Mail::send('emails.channel_partner_deactive', ['params' => $params], function ($m) use ($params) {
							// $m->from($params['from_email'], $params['from_name']);
							// $m->bcc($params['bcc_email']);
							// $m->to($params['to_email'], $params['to_name'])->subject($params['subject']);
						// });

						$Mailarr = [];
						$Mailarr['from_name'] = $configrationForNotify['from_name'];
						$Mailarr['from_email'] = $configrationForNotify['from_email'];
						$Mailarr['to_email'] = $User->email;
						$Mailarr['to_name'] = $configrationForNotify['to_name'];
						$Mailarr['bcc_email'] = implode(',', $bccEmails);
						$Mailarr['cc_email'] = "";
						$Mailarr['subject'] = "Updates: Channel Partner's account deactivated.";
						$Mailarr['transaction_id'] = $User->id;
						$Mailarr['transaction_name'] = "Channel Partner";
						$Mailarr['transaction_type'] = "Email";
						$Mailarr['transaction_detail'] = "emails.channel_partner_deactive";
						$Mailarr['attachment'] = "";
						$Mailarr['remark'] = "Channel Partner Account Deactivated";
						$Mailarr['source'] = "Web";
						$Mailarr['entryip'] = $request->ip();

						if (Config::get('app.env') == 'local') {
							$Mailarr['to_email'] = $configrationForNotify['test_email'];
							$Mailarr['bcc_email'] = implode(', ', $configrationForNotify['test_email_bcc']);
						}

						saveNotificationScheduler($Mailarr);
					}

					// START AXONE WORK

					//TEMPLATE 5
					if ($request->user_id != 0) {
						$params = array();

						// $params['to_email'] = $User->email;
						$Parameter = Parameter::where('code', 'channel-partner-active-email')->first();
						$channelPartnerDeactiveMail = $Parameter->name_value;
						$bccEmails = array();
						if ($channelPartnerDeactiveMail != "") {
							$channelPartnerDeactiveMail = explode(",", $channelPartnerDeactiveMail);
							foreach ($channelPartnerDeactiveMail as $keyM => $valueM) {

								if ($valueM != "") {
									$bccEmails[] = $valueM;
								}
							}
						}

						$configrationForNotify = configrationForNotify();
						// $params['bcc_email'] = array("poonam@whitelion.in");
						// $params['firm_name'] = $ChannelPartner->firm_name;
						// $params['first_name'] = $User->first_name;
						// $params['last_name'] = $User->last_name;
						// $params['mobile_number'] = $User->phone_number;
						// $params['user_email'] = $User->email;
						// $params['city_name'] = getCityName($ChannelPartner->d_city_id);
						// $params['channel_partner_type'] = getChannelPartners()[$User->type]['short_name'];
						// $ChannelPartnerAsignSale_persons = User::select('first_name', 'last_name', 'email', 'phone_number')->where('id', $ChannelPartner->sale_persons)->first();
						// $params['sales_user_name'] = $ChannelPartnerAsignSale_persons->first_name . ' ' . $ChannelPartnerAsignSale_persons->last_name;
						// $params['sales_user_email'] = $ChannelPartnerAsignSale_persons->email;
						// $params['sales_user_mobile'] = $ChannelPartnerAsignSale_persons->phone_number;
						// $SalesUserReporting_manager = User::select('first_name', 'last_name', 'email', 'phone_number')->where('id', $ChannelPartner->sale_persons)->first();
						// $params['reporting_manager_name'] = $SalesUserReporting_manager->first_name . ' ' . $SalesUserReporting_manager->last_name;
						// $params['reporting_manager_mobile'] = $SalesUserReporting_manager->phone_number;
						// $params['reporting_manager_email'] = $SalesUserReporting_manager->email;

						// $params['from_name'] = $configrationForNotify['from_name'];
						// $params['from_email'] = $configrationForNotify['from_email'];
						// $params['to_name'] = $configrationForNotify['to_name'];

						// if (Config::get('app.env') == "local") { // SEND MAIL
						// 	$params['to_email'] = $configrationForNotify['test_email'];
						// 	$params['bcc_email'] = $configrationForNotify['test_email_bcc'];
						// }


						$Mailarr = [];
						$Mailarr['from_name'] = $configrationForNotify['from_name'];
						$Mailarr['from_email'] = $configrationForNotify['from_email'];
						$Mailarr['to_email'] = $User->email;
						$Mailarr['to_name'] = $configrationForNotify['to_name'];
						$Mailarr['bcc_email'] = "poonam@whitelion.in";
						$Mailarr['cc_email'] = "";
						$Mailarr['transaction_id'] = $User->id;
						$Mailarr['transaction_name'] = "Channel Partner";
						$Mailarr['transaction_type'] = "Email";
						$Mailarr['attachment'] = "";
						$Mailarr['source'] = "Web";
						$Mailarr['entryip'] = $request->ip();

						if (Config::get('app.env') == 'local') {
							$Mailarr['to_email'] = $configrationForNotify['test_email'];
							$Mailarr['bcc_email'] = implode(', ', $configrationForNotify['test_email_bcc']);
						}
						                 



						// TO CHANNEL PARTENER NEW SALES USER ASSIGN
						// WHEN CHANGE SALES USER
						if ($isSalesUserSame == 1) {

							// $params['subject'] = "New Sales Executive Assigned ";
							// Mail::send('emails.channel_partner_active_to_sales_user', ['params' => $params], function ($m) use ($params) {
							// 	$m->from($params['from_email'], $params['from_name']);
							// 	$m->bcc($params['bcc_email']);
							// 	$m->to($params['to_email'], $params['first_name'])->subject($params['subject']);
							// });

							$Mailarr['subject'] = "New Sales Executive Assigned";
							$Mailarr['transaction_detail'] = "emails.channel_partner_active_to_sales_user";
							$Mailarr['remark'] = "Channel Partner Active To Sales User";
							saveNotificationScheduler($Mailarr);   


							// NEW CHANNEL PARTENER ASSIGN TO SALES USER
							// $params['subject'] = "New channel partner assigned";
							// Mail::send('emails.new_channel_partener_assign_to_sales_user', ['params' => $params], function ($m) use ($params) {
							// 	$m->from($params['from_email'], $params['from_name']);
							// 	$m->bcc($params['bcc_email']);
							// 	$m->to($params['sales_user_email'], $params['sales_user_name'])->subject($params['subject']);
							// });

							$Mailarr['subject'] = "New channel partner assigned";
							$Mailarr['transaction_detail'] = "emails.new_channel_partener_assign_to_sales_user";
							$Mailarr['remark'] = "New Channel Partner Assign To Sales User";
							saveNotificationScheduler($Mailarr);   
						}
					}

					//TEMPLATE 1 TO 4
					if ($request->user_id == 0) {

						$params = array();

						$params['to_email'] = $User->email;
						$Parameter = Parameter::where('code', 'channel-partner-active-email')->first();
						$channelPartnerDeactiveMail = $Parameter->name_value;
						$bccEmails = array();
						if ($channelPartnerDeactiveMail != "") {
							$channelPartnerDeactiveMail = explode(",", $channelPartnerDeactiveMail);
							foreach ($channelPartnerDeactiveMail as $keyM => $valueM) {

								if ($valueM != "") {
									$bccEmails[] = $valueM;
								}
							}
						}

						$configrationForNotify = configrationForNotify();
						// $params['bcc_email'] = array("poonam@whitelion.in");
						// $params['firm_name'] = $ChannelPartner->firm_name;
						// $params['first_name'] = $User->first_name;
						// $params['last_name'] = $User->last_name;
						// $params['mobile_number'] = $User->phone_number;
						// $params['user_email'] = $User->email;
						// $params['city_name'] = getCityName($ChannelPartner->d_city_id);
						// $params['channel_partner_type'] = getChannelPartners()[$User->type]['short_name'];
						// $ChannelPartnerAsignSale_persons = User::select('first_name', 'last_name', 'email', 'phone_number')->where('id', $ChannelPartner->sale_persons)->first();
						// $params['sales_user_name'] = $ChannelPartnerAsignSale_persons->first_name . ' ' . $ChannelPartnerAsignSale_persons->last_name;
						// $params['sales_user_email'] = $ChannelPartnerAsignSale_persons->email;
						// $params['sales_user_mobile'] = $ChannelPartnerAsignSale_persons->phone_number;
						// $ChannelPartnerReporting_manager = User::select('first_name', 'last_name', 'email', 'phone_number')->where('id', $ChannelPartner->sale_persons)->first();
						// $params['reporting_manager_name'] = $ChannelPartnerReporting_manager->first_name . ' ' . $ChannelPartnerReporting_manager->last_name;
						// $params['reporting_manager_mobile'] = $ChannelPartnerReporting_manager->phone_number;
						// $params['reporting_manager_email'] = $ChannelPartnerReporting_manager->email;

						// $params['from_name'] = $configrationForNotify['from_name'];
						// $params['from_email'] = $configrationForNotify['from_email'];
						// $params['to_name'] = $configrationForNotify['to_name'];

						// if (Config::get('app.env') == "local") { // SEND MAIL
						// 	$params['to_email'] = $configrationForNotify['test_email'];
						// 	$params['bcc_email'] = $configrationForNotify['test_email_bcc'];
						// }


						$Mailarr = [];
						$Mailarr['from_name'] = $configrationForNotify['from_name'];
						$Mailarr['from_email'] = $configrationForNotify['from_email'];
						$Mailarr['to_email'] = $User->email;
						$Mailarr['to_name'] = $configrationForNotify['to_name'];
						$Mailarr['bcc_email'] = "poonam@whitelion.in";
						$Mailarr['cc_email'] = "";
						$Mailarr['transaction_id'] = $User->id;
						$Mailarr['transaction_name'] = "Channel Partner";
						$Mailarr['transaction_type'] = "Email";
						$Mailarr['attachment'] = "";
						$Mailarr['source'] = "Web";
						$Mailarr['entryip'] = $request->ip();

						if (Config::get('app.env') == 'local') {
							$Mailarr['to_email'] = $configrationForNotify['test_email'];
							$Mailarr['bcc_email'] = implode(', ', $configrationForNotify['test_email_bcc']);
						}

						// // TO ASIGN SELES USER
						// $params['subject'] = "New Channel Partner Added to the System";
						// Mail::send('emails.channel_partner_active_to_sales_user', ['params' => $params], function ($m) use ($params) {
						// 	$m->from($params['from_email'], $params['from_name']);
						// 	$m->bcc($params['bcc_email']);
						// 	$m->to($params['sales_user_email'], $params['sales_user_name'])->subject($params['subject']);
						// });

						$Mailarr['subject'] = "New Sales Executive Assigned";
						$Mailarr['transaction_detail'] = "emails.channel_partner_active_to_sales_user";
						$Mailarr['remark'] = "New Channel Partner Executive Assigned";
						saveNotificationScheduler($Mailarr);   

						

						// NEW CHANNEL PARTENER ASSIGN TO SALES USER
						// $params['subject'] = "New channel partner assigned";
						// Mail::send('emails.new_channel_partener_assign_to_sales_user', ['params' => $params], function ($m) use ($params) {
						// 	$m->from($params['from_email'], $params['from_name']);
						// 	$m->bcc($params['bcc_email']);
						// 	$m->to($params['sales_user_email'], $params['sales_user_name'])->subject($params['subject']);
						// });

						$Mailarr['subject'] = "New channel partner assigned";
						$Mailarr['transaction_detail'] = "emails.new_channel_partener_assign_to_sales_user";
						$Mailarr['remark'] = "New Channel Partner Assign To Sales User";
						saveNotificationScheduler($Mailarr);   

						// TO CHANNEL PARTENER
						// $params['subject'] = "Welcome To Whitelion Desk";
						// Mail::send('emails.channel_partner_active_welcome_mail', ['params' => $params], function ($m) use ($params) {
						// 	$m->from($params['from_email'], $params['from_name']);
						// 	$m->bcc($params['bcc_email']);
						// 	$m->to($params['to_email'], $params['first_name'])->subject($params['subject']);
						// });

						$Mailarr['subject'] = "Welcome To Whitelion Desk";
						$Mailarr['transaction_detail'] = "emails.channel_partner_active_welcome_mail";
						$Mailarr['remark'] = "Welcome To Whitelion Desk";
						saveNotificationScheduler($Mailarr); 

						$salePersons = $ChannelPartner->sale_persons;

						$bccEmailUserIds = array();
						if ($salePersons != "") {
							$salePersons = explode(",", $salePersons);
							foreach ($salePersons as $keyS => $valueS) {

								$salesParentsIds = getParentSalePersonsIds($valueS);
								$bccEmailUserIds = array_merge($bccEmailUserIds, $salesParentsIds);
							}
						}

						$bccEmailUserIds = array_unique($bccEmailUserIds);
						$bccEmailUserIds = array_values($bccEmailUserIds);

						$bccEmailUser = User::select('first_name', 'last_name', 'email')->where('id', $bccEmailUserIds)->get();
						foreach ($bccEmailUser as $keyBE => $valueBE) {

							// // $bccEmails[] = $valueBE->email;
							// $params['to_email'] = $valueBE->email;
							// $params['to_name'] = $valueBE->first_name . ' ' . $valueBE->last_name;
							// $params['subject'] = "New Channel Partner Notification";

							// if (Config::get('app.env') == "local") { // SEND MAIL
							// 	$params['to_email'] = $configrationForNotify['test_email'];
							// }

							// // TO SALES USER UPPAR HERARCHY
							// Mail::send('emails.channel_partner_active_uppar_hierarchy', ['params' => $params], function ($m) use ($params) {
							// 	$m->from($params['from_email'], $params['from_name']);
							// 	$m->bcc($params['bcc_email']);
							// 	$m->to($params['to_email'], $params['to_name'])->subject($params['subject']);
							// });

							$Mailarr['to_email'] = $valueBE->email;
							$Mailarr['to_name'] = $valueBE->first_name . ' ' . $valueBE->last_name;
							if (Config::get('app.env') == "local") { // SEND MAIL
								$Mailarr['to_email'] = $configrationForNotify['test_email'];
							}
							$Mailarr['subject'] = "New Channel Partner Notification";
							$Mailarr['transaction_detail'] = "emails.channel_partner_active_uppar_hierarchy";
							$Mailarr['remark'] = "New Channel Partner Notification";
							saveNotificationScheduler($Mailarr); 
						}
					}
					// END AXONE WORK
				}
			}
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}

	public function discountCPTSaveAll(Request $request)
	{

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		if ($isAdminOrCompanyAdmin == 1) {

			$validator = Validator::make($request->all(), [
				'discount_percentage' => ['required'],
				'channel_partner_type_id' => ['required'],
				'product_group_id' => ['required'],
			]);

			if ($validator->fails()) {
				$response = array();
				$response['status'] = 0;
				$response['msg'] = "The request could not be understood by the server due to malformed syntax";
				$response['statuscode'] = 400;
				$response['data'] = $validator->errors();
			} else {
				if ($request->product_group_id != 0) {
					$productBrandIds = array(0);
					$ProductGroup = ProductGroup::select('product_brand')->find($request->product_group_id);
					if ($ProductGroup) {
						if ($ProductGroup->product_brand != "") {
							$productBrandIds = explode(",", $ProductGroup->product_brand);
						}
					}
				}

				$query = ProductInventory::select('id');
				if ($request->product_group_id != 0) {
					$query->whereIn('product_brand_id', $productBrandIds);
				}

				$user_data = ChannelPartner::select('user_id')->where('type', $request->channel_partner_type_id)->get();

				foreach ($user_data as $keycpt => $valuecpt) {
					$ProductInventory = $query->get();
					foreach ($ProductInventory as $keyP => $valueP) {

						$UserDiscount = UserDiscount::where('product_inventory_id', $valueP['id'])->where('user_id', $valuecpt['user_id'])->first();
						if ($UserDiscount) {
							$UserDiscount->discount_percentage = $request->discount_percentage;
							$UserDiscount->save();
						} else {
							$UserDiscount = new UserDiscount();
							$UserDiscount->discount_percentage = $request->discount_percentage;
							$UserDiscount->product_inventory_id = $valueP['id'];
							$UserDiscount->user_id = $valuecpt->user_id;
							$UserDiscount->save();
						}

						$User = User::select('id', 'first_name', 'last_name')->find($valuecpt->user_id);
						$ProductInventory = ProductInventory::select('id', 'description')->find($valueP['id']);

						$debugLog = array();
						$debugLog['name'] = "discount-updated";
						$debugLog['description'] = "#" . $User->id . "(" . $User->first_name . " " . $User->last_name . ") for product #" . $ProductInventory->id . "(" . $ProductInventory->description . ")  has been updated to " . $request->discount_percentage;
						saveDebugLog($debugLog);
					}
				}
				$response = successRes('channel partner type discount updated');
				return response()->json($response)->header('Content-Type', 'application/json');
			}
			return response()->json($response)->header('Content-Type', 'application/json');
		}
	}

	function salePerson(Request $request)
	{

		$isAdminOrCompanyAdmin = isAdminOrCompanyAdmin();
		$isSalePerson = isSalePerson();
		if ($isAdminOrCompanyAdmin == 1) {
			$data = array();
				$query = DB::table('sale_person');
				$query->select('users.id as id', DB::raw('CONCAT(first_name," ", last_name) AS text'));
				$query->leftJoin('users', 'sale_person.user_id', '=', 'users.id');
				//$query->whereIn('sale_person.type', $SalesHierarchyId);
				$query->where('users.type', 2);
				$query->where('users.reference_id', '!=', 0);
				$query->where('users.id', '!=', $request->user_id);

				$q = $request->q;

				$query->where(function ($query) use ($q) {
					$query->where('users.first_name', 'like', '%' . $q . '%');
					$query->orWhere('users.last_name', 'like', '%' . $q . '%');
				});

				$query->limit(5);
				$data = $query->get();
			

			$response = array();
			$response['results'] = $data;
			$response['pagination']['more'] = false;
			return response()->json($response)->header('Content-Type', 'application/json');
		} else if ($isSalePerson == 1) {

			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
			$data = array();
			$query = DB::table('sale_person');
			$query->select('users.id as id', DB::raw('CONCAT(first_name," ", last_name) AS text'));
			$query->leftJoin('users', 'sale_person.user_id', '=', 'users.id');
			//$query->whereIn('sale_person.type', $SalesHierarchyId);
			$query->where('users.type', 2);
			$query->where('users.reference_id', '!=', 0);
			//$query->where('users.id', '!=', $request->user_id);
			$query->whereIn('users.id', $childSalePersonsIds);

			$q = $request->q;

			$query->where(function ($query) use ($q) {
				$query->where('users.first_name', 'like', '%' . $q . '%');
				$query->orWhere('users.last_name', 'like', '%' . $q . '%');
			});

			$query->limit(5);
			$data = $query->get();
		
			$response = array();
			$response['results'] = $data;
			$response['pagination']['more'] = false;
			return response()->json($response)->header('Content-Type', 'application/json');
		}
	}

	public function detail(Request $request)
	{

		$isSalePerson = isSalePerson();

		$User = User::with(
			array(
				'country' => function ($query) {
					$query->select('id', 'name');
				},
				'state' => function ($query) {
					$query->select('id', 'name');
				},
				'city' => function ($query) {
					$query->select('id', 'name');
				},
				'company' => function ($query) {
					$query->select('id', 'name');
				}
			)
		);
		$User->where('id', $request->id);
		$User->whereIn('type', array(101, 102, 103, 104, 105, 106));

		$User = $User->first();
		if ($User) {

			if ($isSalePerson == 1) {

				$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
			}

			$ChannelPartner = ChannelPartner::select('type', 'firm_name', 'reporting_manager_id', 'reporting_company_id', 'sale_persons', 'payment_mode', 'credit_days', 'credit_limit', 'pending_credit', 'gst_number', 'shipping_limit', 'shipping_cost', 'd_address_line1', 'd_address_line2', 'd_pincode', 'd_country_id', 'd_state_id', 'd_city_id', 'data_verified', 'data_not_verified', 'missing_data', 'tele_verified', 'tele_not_verified')->with(
				array(
					'd_country' => function ($query) {
						$query->select('id', 'name');
					},
					'd_state' => function ($query) {
						$query->select('id', 'name');
					},
					'd_city' => function ($query) {
						$query->select('id', 'name');
					}
				)
			);

			if ($isSalePerson == 1) {

				$ChannelPartner->where(function ($query2) use ($childSalePersonsIds) {
					foreach ($childSalePersonsIds as $key => $value) {
						if ($key == 0) {
							$query2->whereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
						} else {
							$query2->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
						}
					}
				});
			}

			$ChannelPartner = $ChannelPartner->where('id', $User->reference_id);
			$ChannelPartner = $ChannelPartner->first();

			if ($ChannelPartner) {
				$User['channel_partner'] = $ChannelPartner;

				if ($User['channel_partner']['reporting_manager_id'] != 0) {

					$query = DB::table('channel_partner');
					$query->leftJoin('users', 'channel_partner.user_id', '=', 'users.id');
					$query->select('channel_partner.type', 'channel_partner.reporting_company_id', 'users.id as id', DB::raw('channel_partner.firm_name AS text'));
					$query->where('channel_partner.user_id', $User['channel_partner']['reporting_manager_id']);
					$ChannelPartner = $query->first();
					$ChannelPartner = json_decode(json_encode($ChannelPartner), true);

					if ($ChannelPartner) {

						$ChannelPartner['id'] = "u-" . $ChannelPartner['id'];
						$ChannelPartner['text'] = $ChannelPartner['text'] . " (" . getUserTypeName($ChannelPartner['type']) . ")";
					}

					$User['channel_partner']['reporting_manager'] = $ChannelPartner;
				} else {

					$Company = array();
					$Company = Company::select('id', 'name as text');
					$Company->where('id', $User['channel_partner']['reporting_company_id']);
					$Company = $Company->first();
					$Company = json_decode(json_encode($Company), true);
					if ($Company) {
						$Company['id'] = "c-" . $Company['id'];
						$Company['text'] = $Company['text'] . " (COMPANY)";
					}
					$User['channel_partner']['reporting_manager'] = $Company;
				}

				$query = DB::table('sale_person');
				$query->select('users.id as id', DB::raw('CONCAT(first_name," ", last_name) AS text'));
				$query->leftJoin('users', 'sale_person.user_id', '=', 'users.id');
				$query->whereIn('users.id', explode(",", $User['channel_partner']['sale_persons']));
				$User['channel_partner']['sale_persons'] = $query->get();

				$response = successRes("Successfully get user");
				$response['data'] = $User;
			} else {
				$response = errorRes("Invalid id");
			}
		} else {
			$response = errorRes("Invalid id");
		}
		return response()->json($response)->header('Content-Type', 'application/json');
	}
	function getDetail(Request $request)
    {
        $selectColumns = ['users.*', 'channel_partner.firm_name', 'channel_partner.sale_persons', 'channel_partner.gst_number', 'channel_partner.reporting_manager_id', 'channel_partner.reporting_company_id', 'channel_partner.tele_verified', 'channel_partner.tele_not_verified', 'channel_partner.firm_name', 'channel_partner.data_verified', 'channel_partner.data_not_verified', 'channel_partner.missing_data', 'sales.first_name', 'city_list.name as city_name', 'state_list.name as state_name', 'country_list.name as country_name'];
        $Architect = User::select($selectColumns);
        $Architect->selectRaw('CONCAT(users.first_name," ",users.last_name) AS account_name');
        $Architect->selectRaw('CONCAT(sales.first_name," ",sales.last_name) AS account_owner');
        $Architect->selectRaw('CONCAT(created_by_user.first_name," ",created_by_user.last_name) AS created_by');
        $Architect->leftJoin('channel_partner', 'channel_partner.user_id', '=', 'users.id');
        $Architect->leftJoin('users as sales', 'sales.id', '=', 'channel_partner.sale_persons');
        $Architect->leftJoin('users as created_by_user', 'users.created_by', '=', 'created_by_user.id');
        $Architect->leftJoin('city_list', 'city_list.id', '=', 'users.city_id');
        $Architect->leftJoin('state_list', 'state_list.id', '=', 'city_list.state_id');
        $Architect->leftJoin('country_list', 'country_list.id', '=', 'city_list.country_id');
        $Architect->where('channel_partner.user_id', $request->id);
        $Architect = $Architect->first();

        // $User  = User::find($Architect->duplicate_from);


        $data = [];
		
        if ($Architect) {
            $Architect = json_encode($Architect);
            $Architect = json_decode($Architect, true);

            $data['user'] = $Architect;
            // if ($User) {
            //     $data['user']['duplicate_from'] = $User;
            // } else {
                $data['user']['duplicate_from'] = "";
            // }

            $data['user']['user_type_lable'] = '';
            if ($Architect['type'] == 201) {
                $data['user']['user_type_lable'] = '<span class="badge rounded-pill bg-success ms-2" style="font-size: 12px;">NON-PRIME</span>';
            } elseif ($Architect['type'] == 202) {
                $data['user']['user_type_lable'] = '<span class="badge rounded-pill bg-success ms-2" style="font-size: 12px;">PRIME</span>';
            }

            $data['user']['user_status_lable'] = '<span style="height: fit-content;">' . getUserStatusLable($Architect['status']) . '</span>';

            if ($data['user']['tag'] != null) {
                $CRMLeadDealTag = DB::table('tag_master');
                $CRMLeadDealTag->select('tag_master.id AS id', 'tag_master.tagname AS text');
                $CRMLeadDealTag->where('tag_master.isactive', 1);
                $CRMLeadDealTag->where('tag_master.tag_type', 202);
                $CRMLeadDealTag->whereIn('tag_master.id', explode(',', $data['user']['tag']));
                $data['user']['tag'] = $CRMLeadDealTag->get();
            } else {
                $data['user']['tag'] = '';
            }

            $data['user']['sale_person'] = array();
            if ($data['user']['sale_persons'] != null) {
                $data['user']['sale_person']['id'] = $data['user']['sale_persons'];
                $data['user']['sale_person']['text'] = $data['user']['account_owner'];
            } else {
                $data['user']['sale_person']['id'] = "";
                $data['user']['sale_person']['text'] = "";
            }

            $created_at = date('d/m/Y g:i A', strtotime($data['user']['created_at']));
            $updated_at = date('d/m/Y g:i A', strtotime($data['user']['updated_at']));

            // $data['user'] = $Architect;
            $data['user']['created_at1'] = $created_at;
            $data['user']['updated_at1'] = $updated_at;
            // $data['user']['tag'] = $CRMLeadDealTag->get();

            $data['is_architect'] = 1;
            $data['is_electrician'] = 0;

            $data['contacts'] = getUserContactList($data['user']['id'])['data'];
            $data['updates'] = getUserNoteList($data['user']['id'])['data'];
            $data['files'] = getUserFileList($data['user']['id'])['data'];

            $data['calls'] = getUserAllOpenList($data['user']['id'])['call_data'];
            $data['meetings'] = getUserAllOpenList($data['user']['id'])['meeting_data'];
            $data['tasks'] = getUserAllOpenList($data['user']['id'])['task_data'];
            $data['max_open_actions'] = getUserAllOpenList($data['user']['id'])['max_open_actions'];

            $data['calls_closed'] = getUserAllCloseList($data['user']['id'])['close_call_data'];
            $data['meetings_closed'] = getUserAllCloseList($data['user']['id'])['close_meeting_data'];
            $data['tasks_closed'] = getUserAllCloseList($data['user']['id'])['close_task_data'];
            $data['max_close_actions'] = getUserAllCloseList($data['user']['id'])['max_close_actions'];

            $data['timeline'] = getUserTimelineList($data['user']['id'])['data'];

            $data['point_log'] = getUserPointLogList($data['user']['id'])['view'];
            $data['user_log'] = getUserLogList($data['user']['id'])['view'];

            $response = successRes('Get List');
            $response['view'] = view('channel_partners_new/detail', compact('data'))->render();
            $response['data'] = $data;
        } else {
            $response = errorRes('Lead Data Not Available');
        }

        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }
	function export(Request $request)
	{

		$isSalePerson = isSalePerson();
		$channelPartners = getChannelPartners();

		if ($isSalePerson == 1) {

			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
		}

		$columns = array(
			'users.id',
			'users.first_name',
			'users.last_name',
			'users.email',
			'users.dialing_code',
			'users.phone_number',
			'users.status',
			'users.created_at',
			'channel_partner.firm_name',
			'channel_partner.reporting_manager_id',
			'channel_partner.reporting_company_id',
			'channel_partner.sale_persons',
			'channel_partner.payment_mode',
			'channel_partner.credit_days',
			'channel_partner.credit_limit',
			'channel_partner.pending_credit',
			'channel_partner.gst_number',
			'channel_partner.shipping_limit',
			'channel_partner.shipping_cost',
			'channel_partner.d_address_line1',
			'channel_partner.d_address_line2',
			'channel_partner.d_pincode',
			'channel_partner.d_country_id',
			'channel_partner.d_state_id',
			'channel_partner.d_city_id',

		);

		$query = DB::table('users');
		$query->select($columns);
		$query->leftJoin('channel_partner', 'channel_partner.id', '=', 'users.reference_id');
		$query->where('users.type', $request->type);
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
		$query->orderBy('id', 'desc');
		$data = $query->get();

		$headers = array("#ID", "Firstname", "Lastname", "Email", "Phone", "Status", "Created", "Firm Name", "Bill To", "Assign Sales Persons", "Payment Mode", "GST Number", "Shipping Limit", "Shipping Cost", "Delivery Address - Country ", "Delivery Address - State ", "Delivery Address - City ", "Delivery Address - Pincode ", "Delivery Address - Address line 1 ", "Delivery Address - Address line 2 ");

		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="' . $channelPartners[$request->type]['short_name'] . '.csv"');
		$fp = fopen('php://output', 'wb');

		fputcsv($fp, $headers);

		foreach ($data as $key => $value) {

			$createdAt = convertDateTime($value->created_at);
			$status = $value->status;
			if ($status == 0) {
				$status = "Inactive";
			} else if ($status == 1) {
				$status = "Active";
			} else if ($status == 2) {
				$status = "Blocked";
			}

			$billTo = "";
			if ($value->reporting_manager_id != 0) {

				$ChannelPartner = ChannelPartner::select('firm_name', 'type');
				$ChannelPartner->where('user_id', $value->reporting_manager_id);
				$ChannelPartner = $ChannelPartner->first();
				if ($ChannelPartner) {

					$billTo = $ChannelPartner->firm_name;
				}
			} else {

				$Company = array();
				$Company = Company::select('id', 'name');
				$Company->where('id', $value->reporting_company_id);
				$Company = $Company->first();
				if ($Company) {
					$billTo = $Company->name;
				}
			}

			$StrsalePersons = "";

			$salePersons = DB::table('sale_person');
			$salePersons->select('users.id as id', DB::raw('CONCAT(first_name," ", last_name) AS text'));
			$salePersons->leftJoin('users', 'sale_person.user_id', '=', 'users.id');
			$salePersons->whereIn('users.id', explode(",", $value->sale_persons));
			$salePersons = $salePersons->get();

			$StrsalePersons = "";
			foreach ($salePersons as $keySP => $valueSP) {

				$StrsalePersons .= $valueSP->text;
			}

			$paymentMode = "";

			if ($value->payment_mode == 0) {
				$paymentMode = "PDC";
			} else if ($value->payment_mode == 1) {
				$paymentMode = "ADVANCE";
			} else if ($value->payment_mode == 2) {
				$paymentMode = "CREDIT";
			}

			$countryName = getCountryName($value->d_country_id);
			$stateName = getStateName($value->d_state_id);
			$cityName = getCityName($value->d_city_id);

			$lineVal = array(
				$value->id,
				$value->first_name,
				$value->last_name,
				$value->email,
				$value->dialing_code . " " . $value->phone_number,
				$status,
				$createdAt,
				$value->firm_name,
				$billTo,
				$StrsalePersons,
				$paymentMode,
				$value->gst_number,
				$value->shipping_limit,
				$value->shipping_cost,
				$countryName,
				$stateName,
				$cityName,
				$value->d_pincode,
				$value->d_address_line1,
				$value->d_address_line2,

			);

			fputcsv($fp, $lineVal, ",");
		}

		fclose($fp);
	}
}
