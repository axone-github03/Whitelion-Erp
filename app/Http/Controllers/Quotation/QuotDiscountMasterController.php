<?php

namespace App\Http\Controllers\Quotation;

use Illuminate\Http\Request;
use App\Models\SalesHierarchy;
use App\Models\PurchaseHierarchy;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\WlmstQuotDiscountFlow;
use App\Models\WlmstQuotDiscountFlowItem;
use Illuminate\Support\Facades\Validator;


class QuotDiscountMasterController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {

            $tabCanAccessBy = array(0, 1, 2, 9, 101, 102, 103, 104, 105, 13);

            if (!in_array(Auth::user()->type, $tabCanAccessBy)) {
                return redirect()->route('dashboard');
            }
            return $next($request);
        });
    }

    public function index()
	{
		$data = array();
		$data['title'] = "Discount Flow Master ";
        return view('quotation/master/discount/index', compact('data'));
	}

	public function searchUserType(Request $request) {
		$results = SalesHierarchy::select('id', 'name as text');
		$results->where('id', '!=', $request->id);
		$results->where('name', 'like', "%" . $request->q . "%");
		$results->limit(15);
		$results = $results->get();
	
		$channelPartner['id'] = 999;
		$channelPartner['text'] = 'Channel Partner';
		$results->push($channelPartner);
	
		$response = array();
        $response['results'] = $results;
		$response['pagination']['more'] = false;
	
		return response()->json($response)->header('Content-Type', 'application/json');
	}
	

    public function searchUser(Request $request) {
		$results = [];
	
		if ($request->has('user_type_id')) {
			// Check if the selected user type is "Channel Partner"
			if ($request->user_type_id == 999) {
				// Fetch users based on the channel partner's ID
				$channelPartnerUsers = DB::table('channel_partner')
				->join('users', 'channel_partner.user_id', '=', 'users.id')
				->select('users.id', DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS text"))
				->where('users.id', '!=', $request->id)
				->where(function ($query) use ($request) {
					$query->where('users.first_name', 'like', "%" . $request->q . "%")
						->orWhere('users.last_name', 'like', "%" . $request->q . "%");
				})
				->limit(15)
				->get();
	
				// Merge channel partner users with the results
				$results = $channelPartnerUsers->toArray();
			} else {
				// Fetch users based on the selected user type ID
				$results = DB::table('users')
					->leftJoin('sale_person', 'sale_person.id', '=', 'users.reference_id')
					->leftJoin('sales_hierarchy', 'sales_hierarchy.id', '=', 'sale_person.type')
					->select('users.id', DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS text"))
					->where('users.id', '!=', $request->id)
					->where(function ($query) use ($request) {
						$query->where('users.first_name', 'like', "%" . $request->q . "%")
							->orWhere('users.last_name', 'like', "%" . $request->q . "%");
					})
					->where('sales_hierarchy.id', $request->user_type_id)
					->limit(5)
					->get();
			}
		}
	
		$response = [
			'results' => $results,
			'pagination' => ['more' => false]
		];
	
		return response()->json($response)->header('Content-Type', 'application/json');
	}

    public function saveProcess(Request $request) {
		// Step 1: Validation
		$validator = Validator::make($request->all(), [
			'isDisName' => ['required'],
			'isDisDefault' => ['required', 'numeric', 'between:0,100'],
			'isDisUser' => ['required', 'numeric', 'between:0,100'],
			'isDisStatus' => ['required'],
			'AdvanceData' => ['required'],
		]);
	
		if ($validator->fails()) {
			$errors = $validator->errors()->all();
			$response = errorRes(implode(', ', $errors));
			return response()->json($response)->header('Content-Type', 'application/json');
		}

		if ($request->isDisFlow != 0) {
			// Update existing discount flow
			$discountFlow = WlmstQuotDiscountFlow::find($request->isDisFlow);
			if (!$discountFlow) {
				// If the discount flow doesn't exist, return an error response
				$response = errorRes("Discount flow not found");
				return response()->json($response)->header('Content-Type', 'application/json');
			}
		
			// Update the discount flow attributes
			$discountFlow->name = $request->isDisName;
			$discountFlow->default_discount = $request->isDisDefault;
			$discountFlow->user_discount = $request->isDisUser;
			$discountFlow->status = $request->isDisStatus;
			$discountFlow->remark = '';
			$discountFlow->save();
		
			// Delete existing discount flow items
			WlmstQuotDiscountFlowItem::where('dis_flow_id', $discountFlow->id)->delete();
		
			// Create new discount flow items
			foreach (json_decode($request->AdvanceData, true) as $filt_value) {
				$userIds = implode(',', $filt_value['discount_user_id']);
		
				$discountFlowItem = new WlmstQuotDiscountFlowItem();
				$discountFlowItem->dis_flow_id = $discountFlow->id;
				$discountFlowItem->user_type = $filt_value['discount_user_type_id'];
				$discountFlowItem->user_id = $userIds;
				$discountFlowItem->discount = $filt_value['discount_dis'];
				$discountFlowItem->status = $request->isDisStatus;
				$discountFlowItem->remark = '';
				$discountFlowItem->save();
			}
		
			// Prepare success response
			$response = successRes("Successfully updated discount");
		} else {
			$DiscountFlow = new WlmstQuotDiscountFlow();
			$DiscountFlow->name = $request->isDisName;
			$DiscountFlow->code = 0;
			$DiscountFlow->default_discount = $request->isDisDefault;
			$DiscountFlow->user_discount = $request->isDisUser;
			$DiscountFlow->status = $request->isDisStatus;
			$DiscountFlow->remark = '';
			$DiscountFlow->save();

			// Create new discount flow item
			foreach (json_decode($request->AdvanceData, true) as $key => $filt_value) {
				$userIds = implode(',', $filt_value['discount_user_id']);

				$DiscountFlowItem = new WlmstQuotDiscountFlowItem();
				$DiscountFlowItem->dis_flow_id = $DiscountFlow->id;
				$DiscountFlowItem->user_type = $filt_value['discount_user_type_id'];
				$DiscountFlowItem->user_id = $userIds;
				$DiscountFlowItem->discount = $filt_value['discount_dis'];
				$DiscountFlowItem->status = $request->isDisStatus; // You may adjust this based on your requirement
				$DiscountFlowItem->remark = ''; // You may adjust this based on your requirement
				$DiscountFlowItem->save();
			}

			// Prepare success response
			$response = successRes("Successfully added discount");
		}
	
		return response()->json($response)->header('Content-Type', 'application/json');
	}
	public function ajax(Request $request) {
		$searchColumns = array(
			0 => 'wlmst_quot_discount_flow.id',
			1 => 'wlmst_quot_discount_flow.name',
		);
	
		$columns = array(
			0 => 'wlmst_quot_discount_flow.id',
			1 => 'wlmst_quot_discount_flow.name',
			2 => 'wlmst_quot_discount_flow.status',
			3 => 'wlmst_quot_discount_flow.default_discount',
			4 => 'wlmst_quot_discount_flow.user_discount',
		);
	
		$query = WlmstQuotDiscountFlow::query();
		$query->select($columns);
	
		// Pagination
		$recordsTotal = $query->count();
		$query->orderBy($columns[$request['order'][0]['column']], $request['order'][0]['dir']);
		$query->limit($request->length)->offset($request->start);
	
		// Search filter
		$search_value = '';
		if (isset($request['search']['value'])) {
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
	
		$responseData = [];
		foreach ($data as $entry) {
			$rowData['id'] = highlightString($entry['id'], $search_value);
			$rowData['name'] = highlightString($entry['name'], $search_value);
			$rowData['status'] = getSalesHierarchyStatusLable($entry['status']);
			$rowData['default_discount'] = highlightString($entry['default_discount'], $search_value);
			$rowData['user_discount'] = highlightString($entry['user_discount'], $search_value);
	
			$uiAction = '<ul class="list-inline font-size-20 contact-links mb-0">';
			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="editView(\'' . $entry['id'] . '\')" href="javascript: void(0);" title="Edit"><i class="bx bx-edit-alt"></i></a>';
			$uiAction .= '</li>';
			$uiAction .= '<li class="list-inline-item px-2">';
			$uiAction .= '<a onclick="deleteWarning(\'' . $entry['id'] . '\')" href="javascript: void(0);" title="Delete"><i class="bx bx-trash-alt"></i></a>';
			$uiAction .= '</li>';
			$uiAction .= '</ul>';
			$rowData['action'] = $uiAction;
	
			$responseData[] = $rowData;
		}
	
		$jsonResponse = array(
			"draw" => intval($request['draw']),
			"recordsTotal" => intval($recordsTotal),
			"recordsFiltered" => intval($recordsTotal), // Assuming no filtering is done on grouped data
			"data" => $responseData,
		);
	
		return response()->json($jsonResponse)->header('Content-Type', 'application/json');
	}
	
	public function delete(Request $request) {

		$DiscountFlow = WlmstQuotDiscountFlow::find($request->id);
		if ($DiscountFlow) {
			WlmstQuotDiscountFlowItem::where('dis_flow_id', $DiscountFlow->id)->delete();
			$debugLog = array();
			$debugLog['name'] = "discount-delete";
			$debugLog['description'] = "discount #" . $DiscountFlow->id . "(" . $DiscountFlow->name . ") has been deleted";
			saveDebugLog($debugLog);

			$DiscountFlow->delete();

		}
		$response = successRes("Successfully delete discount");
		return response()->json($response)->header('Content-Type', 'application/json');

	}

	public function detail(Request $request)
	{    
		if ($request->has('id')) {
			$id = $request->id;
			$MainMaster = WlmstQuotDiscountFlowItem::with(['usertype' => function ($query) {
				$query->select('id', 'name');
			}])
			->leftJoin('wlmst_quot_discount_flow', 'wlmst_quot_discount_flow.id', '=', 'wlmst_quot_discount_flow_items.dis_flow_id')
			->where('dis_flow_id', $id)
			->get();

			if ($MainMaster->isNotEmpty()) {
				$responseData = [];

				// Group the data by dis_flow_id
				$groupedData = $MainMaster->groupBy('dis_flow_id');

				foreach ($groupedData as $disFlowId => $items) {
					$itemData = [];
					$itemData['dis_flow_id'] = $disFlowId;

					foreach ($items as $item) {
						$query_user_id = DB::table('users')
							->select('users.id AS id', DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS text"))
							->whereIn('users.id', explode(",", $item->user_id))
							->get();

						$item->user_id = $query_user_id->toArray();

						// Append each item to its respective categories
						$itemData['items'][] = $item->toArray();
					}

					$responseData[] = $itemData;
				}

				$response = successRes("Successfully get service item tag master");
				$response['data'] = $responseData;

			} else {
				$response = errorRes("No data found");
			}
		} else {
			$response = errorRes("ID parameter is missing");
		}

		return response()->json($response)->header('Content-Type', 'application/json');
	}
	
}
