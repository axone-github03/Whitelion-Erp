<?php

namespace App\Http\Controllers;

use App\Models\ChannelPartner;
use App\Models\Company;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

//use Session;

class ChannelPartnersViewController extends Controller
{

	public function __construct()
	{

		$this->middleware(function ($request, $next) {

			$tabCanAccessBy = array(2, 3, 7);

			if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
				return redirect()->route('dashboard');
			}

			return $next($request);
		});
	}

	public function stockist(Request $request)
	{

		$data = array();
		$data['title'] = "Stockist - Channel Partners";
		$data['type'] = 101;
		return view('channel_partners/view', compact('data'));
	}

	public function adm(Request $request)
	{

		$data = array();
		$data['title'] = "ADM - Channel Partners";
		$data['type'] = 102;
		return view('channel_partners/view', compact('data'));
	}

	public function apm(Request $request)
	{

		$data = array();
		$data['title'] = "APM - Channel Partners";
		$data['type'] = 103;
		return view('channel_partners/view', compact('data'));
	}
	public function ad(Request $request)
	{

		$data = array();
		$data['title'] = "AD - Channel Partners";
		$data['type'] = 104;
		return view('channel_partners/view', compact('data'));
	}

	public function retailer(Request $request)
	{

		$data = array();
		$data['title'] = "Retailer";
		$data['type'] = 105;
		return view('channel_partners/view', compact('data'));
	}
	public function afm(Request $request)
	{

		$data = array();
		$data['title'] = "AFM - Channel Partners";
		$data['type'] = 106;
		return view('channel_partners/view', compact('data'));
	}
	public function ajax(Request $request)
	{

		$isSalePerson = isSalePerson();

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
			'users.id',
			'users.first_name',
			'users.email',
			'users.last_name',
			'users.type',
			'users.status',
			'users.phone_number',
			'channel_partner.gst_number',
			'channel_partner.reporting_manager_id',
			'channel_partner.reporting_company_id',
			'channel_partner.sale_persons',
			'channel_partner.firm_name',

		);

		$sortColumns = array(
			0 => 'users.id',
			1 => 'users.first_name',
			2 => 'users.email',
			3 => 'channel_partner.reporting_company_id',
			4 => 'channel_partner.sale_persons',
			5 => 'users.last_active_date_time',

		);

		$recordsTotal = DB::table('users');
		$recordsTotal->leftJoin('channel_partner', 'channel_partner.id', '=', 'users.reference_id');
		$recordsTotal->where('users.type', $request->type);
		if ($isSalePerson == 1) {

			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);
			$recordsTotal->where(function ($query) use ($childSalePersonsIds) {
				foreach ($childSalePersonsIds as $key => $value) {
					if ($key == 0) {
						$query->whereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
					} else {
						$query->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
					}
				}
			});

			// $recordsTotal->where(function ($query) use ($childSalePersonsIds) {

			// 	foreach ($childSalePersonsIds as $key => $value) {
			// 		if ($key == 0) {
			// 			$query->whereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
			// 		} else {
			// 			$query->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
			// 		}

			// 	}

			// });

		} else if (Auth::user()->type == 3) {

			if (Auth::user()->parent_id != 0) {

				$recordsTotal->where('users.id', Auth::user()->parent_id);
			} else {

				$recordsTotal->where('channel_partner.reporting_company_id', Auth::user()->company_id);
			}
		}

		$recordsTotal = $recordsTotal->count();
		$recordsFiltered = $recordsTotal; // when there is no search parameter then total number rows = total number filtered rows.

		$query = DB::table('users');
		$query->select($selectColumns);
		$query->leftJoin('channel_partner', 'channel_partner.id', '=', 'users.reference_id');
		$query->where('users.type', $request->type);
		if (Auth::user()->type == 2) {
			// $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

			// $query->where(function ($query) use ($childSalePersonsIds) {

			// 	foreach ($childSalePersonsIds as $key => $value) {
			// 		if ($key == 0) {
			// 			$query->whereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
			// 		} else {
			// 			$query->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
			// 		}

			// 	}

			// });

			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

			$query->where(function ($query2) use ($childSalePersonsIds) {
				foreach ($childSalePersonsIds as $key => $value) {
					if ($key == 0) {
						$query2->whereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
					} else {
						$query2->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
					}
				}
			});
		} else if (Auth::user()->type == 3) {

			if (Auth::user()->parent_id != 0) {

				$query->where('users.id', Auth::user()->parent_id);
			} else {

				$query->where('channel_partner.reporting_company_id', Auth::user()->company_id);
			}

			// $query->where('channel_partner.reporting_company_id', Auth::user()->company_id);

		}
		$query->whereIn('users.type', array(101, 102, 103, 104, 105));
		// $query->limit($request->length);
		// $query->offset($request->start);
		$query->orderBy($sortColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
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

		$recordsFiltered = $query->count();
		$query = DB::table('users');
		$query->select($selectColumns);
		$query->leftJoin('channel_partner', 'channel_partner.id', '=', 'users.reference_id');
		$query->where('users.type', $request->type);
		if (Auth::user()->type == 2) {
			// $childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

			// $query->where(function ($query) use ($childSalePersonsIds) {

			// 	foreach ($childSalePersonsIds as $key => $value) {
			// 		if ($key == 0) {
			// 			$query->whereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
			// 		} else {
			// 			$query->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
			// 		}

			// 	}

			// });

			$childSalePersonsIds = getChildSalePersonsIds(Auth::user()->id);

			$query->where(function ($query2) use ($childSalePersonsIds) {
				foreach ($childSalePersonsIds as $key => $value) {
					if ($key == 0) {
						$query2->whereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
					} else {
						$query2->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
					}
				}
			});
		} else if (Auth::user()->type == 3) {

			if (Auth::user()->parent_id != 0) {

				$query->where('users.id', Auth::user()->parent_id);
			} else {

				$query->where('channel_partner.reporting_company_id', Auth::user()->company_id);
			}

			// $query->where('channel_partner.reporting_company_id', Auth::user()->company_id);

		}
		$query->whereIn('users.type', array(101, 102, 103, 104, 105));
		$query->limit($request->length);
		$query->offset($request->start);
		$query->orderBy($sortColumns[$request['order'][0]['column']], $request['order'][0]['dir']);
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
		// echo "<pre>";
		// print_r(DB::getQueryLog());
		// die;

		$data = json_decode(json_encode($data), true);

		// if ($isFilterApply == 1) {
		// 	$recordsFiltered = count($data);
		// }

		foreach ($data as $key => $value) {

			$data[$key]['id'] = '<div class="avatar-xs"><span class="avatar-title rounded-circle">' . $data[$key]['id'] . '</span></div>';

			$data[$key]['name'] = '<h5 class="font-size-14 mb-1"><a href="javascript: void(0);" class="text-dark">' . $value['firm_name'] . '</a></h5>
             <p class="text-muted mb-0">' . getUserTypeName($value['type']) . '</p>';

			// if ($data[$key]['created_at'] == $data[$key]['last_active_date_time']) {

			// 	$data[$key]['last_active_date_time'] = "-";
			// 	$data[$key]['last_login_date_time'] = "-";

			// } else {

			// 	$data[$key]['last_active_date_time'] = convertDateTime($value['last_active_date_time']);
			// 	$data[$key]['last_login_date_time'] = convertDateTime($value['last_login_date_time']);

			// }

			// $data[$key]['active_login'] = '<p class="text-muted mb-0">' . $data[$key]['last_active_date_time'] . '</p>
			//           <p class="text-muted mb-0">' . $data[$key]['last_login_date_time'] . '</p>';

			$data[$key]['status'] = getUserStatusLable($value['status']);
			$data[$key]['email'] = '<p class="text-muted mb-0">' . $value['email'] . '</p>
             <p class="text-muted mb-0">' . $value['phone_number'] . '</p><p class="text-muted mb-0">' . ($value['gst_number']) . '</p>';

			$invoiceFrom = "";

			if ($value['reporting_manager_id'] != 0) {

				$ChannelPartner = ChannelPartner::select('firm_name', 'type');
				$ChannelPartner->where('user_id', $value['reporting_manager_id']);
				$ChannelPartner = $ChannelPartner->first();
				if ($ChannelPartner) {

					$invoiceFrom = '<p class="text-muted mb-0">' . $ChannelPartner->firm_name . '</p>
             <p class="text-muted mb-0">' . getUserTypeName($ChannelPartner->type) . '</p>';
				}
			} else {

				$Company = array();
				$Company = Company::select('id', 'name');
				$Company->where('id', $value['reporting_company_id']);
				$Company = $Company->first();
				if ($Company) {
					$invoiceFrom = '<p class="text-muted mb-0">' . $Company->name . '</p>';
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

				$StrsalePersons .= '<p class="text-muted mb-0">' . $valueSP->text . '</p>';
			}

			$data[$key]['sale_persons'] = $StrsalePersons;

			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';

			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="userView(\'' . $value['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="mdi mdi-eye"></i></a>';
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
	public function detail(Request $request)
	{

		$User = User::with(array('country' => function ($query) {
			$query->select('id', 'name');
		}, 'state' => function ($query) {
			$query->select('id', 'name');
		}, 'city' => function ($query) {
			$query->select('id', 'name');
		}, 'company' => function ($query) {
			$query->select('id', 'name');
		}))->where('id', $request->id)->whereIn('type', array(101, 102, 103, 104, 105))->first();
		if ($User) {

			$User['channel_partner'] = ChannelPartner::select('type', 'firm_name', 'reporting_manager_id', 'reporting_company_id', 'sale_persons', 'payment_mode', 'credit_days', 'credit_limit', 'pending_credit', 'gst_number', 'shipping_limit', 'shipping_cost', 'd_address_line1', 'd_address_line2', 'd_pincode', 'd_country_id', 'd_state_id', 'd_city_id')->with(array('d_country' => function ($query) {
				$query->select('id', 'name');
			}, 'd_state' => function ($query) {
				$query->select('id', 'name');
			}, 'd_city' => function ($query) {
				$query->select('id', 'name');
			}))->find($User->reference_id);

			if ($User['channel_partner']['reporting_manager_id'] != 0) {

				$query = DB::table('channel_partner');
				$query->leftJoin('users', 'channel_partner.user_id', '=', 'users.id');
				$query->select('channel_partner.reporting_company_id', 'users.id as id', DB::raw('firm_name AS text'));
				$query->where('channel_partner.user_id', $User['channel_partner']['reporting_manager_id']);
				$ChannelPartner = $query->first();
				$ChannelPartner = json_decode(json_encode($ChannelPartner), true);

				if ($ChannelPartner) {

					$companyName = "";
					$Company = Company::select('name')->find($ChannelPartner['reporting_company_id']);
					if ($Company) {

						$companyName = " (" . $Company->name . ")";
					}

					//$ChannelPartner['id']=$ChannelPartner->id;
					$ChannelPartner['id'] = "u-" . $ChannelPartner['id'];
					$ChannelPartner['text'] = $ChannelPartner['text'] . $companyName;
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
		return response()->json($response)->header('Content-Type', 'application/json');
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
		// if ($isSalePerson == 1) {

		// 	$query->where(function ($query2) use ($childSalePersonsIds) {
		// 		foreach ($childSalePersonsIds as $key => $value) {
		// 			if ($key == 0) {
		// 				$query2->whereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
		// 			} else {
		// 				$query2->orWhereRaw('FIND_IN_SET("' . $value . '",channel_partner.sale_persons)>0');
		// 			}
		// 		}
		// 	});

		// }

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
		} else if (Auth::user()->type == 3) {

			if (Auth::user()->parent_id != 0) {

				$query->where('users.id', Auth::user()->parent_id);
			} else {

				$query->where('channel_partner.reporting_company_id', Auth::user()->company_id);
			}
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
