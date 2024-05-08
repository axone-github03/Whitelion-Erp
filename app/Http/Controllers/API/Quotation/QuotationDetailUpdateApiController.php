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
class QuotationDetailUpdateApiController extends Controller
{
    public function quotRoomNBoardStatusList(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'quot_id' => ['required'],
            'quotgroup_id' => ['required'],
        ]);

        if ($validator->fails()) {
            $response = errorRes($validator->errors()->first());
            $response['data'] = $validator->errors();
        } else {
            $room_columns = ['wltrn_quot_itemdetails.quot_id', 'wltrn_quot_itemdetails.quotgroup_id', 'wltrn_quot_itemdetails.room_no', 'wltrn_quot_itemdetails.room_name', 'wltrn_quot_itemdetails.isactiveroom'];

            $room_query = Wltrn_QuotItemdetail::query();
            $room_query->select($room_columns);
            $room_query->selectRaw('case when wltrn_quot_itemdetails.isactiveroom = 1 then "Active" else "Inactive" end as status');
            $room_query->where('wltrn_quot_itemdetails.quot_id', $request->quot_id);
            $room_query->where('wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id);
            $room_query->groupBy($room_columns);
            $room_query->orderBy('wltrn_quot_itemdetails.room_no', 'ASC');
            $roomlist = $room_query->get();
            foreach ($roomlist as $key => $value) {
                $roomlist[$key]['isroom'] = ($value->isactiveroom == 1) ? true : false;
                
                $board_columns = ['wltrn_quot_itemdetails.board_no', 'wltrn_quot_itemdetails.board_name', 'wltrn_quot_itemdetails.item_type', 'wltrn_quot_itemdetails.isactiveboard'];
                
                $board_query = Wltrn_QuotItemdetail::query();
                $board_query->select($board_columns);
                $board_query->selectRaw('CONCAT(board_name," (", item_type,")") AS board_title');
                $board_query->selectRaw('case when wltrn_quot_itemdetails.isactiveboard = 1 then "Active" else "Inactive" end as status');
                $board_query->where('wltrn_quot_itemdetails.quot_id', $value->quot_id);
                $board_query->where('wltrn_quot_itemdetails.quotgroup_id', $value->quotgroup_id);
                $board_query->where('wltrn_quot_itemdetails.room_no', $value->room_no);
                $board_query->where('wltrn_quot_itemdetails.board_no', '<>', 0);
                $board_query->groupBy($board_columns);
                $board_query->orderBy('wltrn_quot_itemdetails.board_no', 'ASC');
                $board_list = $board_query->get();
                foreach ($board_list as $board_key => $board_value) {
                    $board_list[$board_key]['isboard'] = ($board_value->isactiveboard == 1) ? true : false;
                }

                $roomlist[$key]['board'] = $board_list;
            }
            $response = successRes('Successfully get list of room & board');
            $response['data'] = $roomlist;
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    public function saveQuotBulkRoomBoardStatus(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'body' => ['required'],
        ]);

        if ($validator->fails()) {
            $response = errorRes($validator->errors()->first());
            $response['data'] = $validator->errors();
        } else {
            $request_data = $request->all()['body'];
            
            foreach ($request_data as $room_key => $room_value) {
                $group_id = $room_value['quot_id'];
                $quotgroup_id = $room_value['quotgroup_id'];
                $room_no = $room_value['room_no'];
                
                $update_board_status = Wltrn_QuotItemdetail::where('quot_id', $group_id);
                $update_board_status->where('quotgroup_id', $quotgroup_id);
                $update_board_status->where('room_no', $room_no);
                $update_board_status->update(['isactiveroom' => $room_value['isactiveroom']]);

                foreach ($room_value['board'] as $board_key => $board_value) {
                    $board_no = $board_value['board_no'];
                    if ($board_value['isactiveboard'] == 1) {
                        $update_board_status = Wltrn_QuotItemdetail::where('quot_id', $group_id);
                        $update_board_status->where('quotgroup_id', $quotgroup_id);
                        $update_board_status->where('room_no', $room_no);
                        $update_board_status->update(['isactiveroom' => $room_value['isactiveroom']]);
                    }
                    $update_board_status = Wltrn_QuotItemdetail::where('quot_id', $group_id);
                    $update_board_status->where('quotgroup_id', $quotgroup_id);
                    $update_board_status->where('room_no', $room_no);
                    $update_board_status->where('board_no', $board_no);
                    $update_board_status->update(['isactiveboard' => $board_value['isactiveboard']]);
                    
                }
            }
            $response = successRes('Successfully update room & board Status');
            $response['data'] = array();
        }
        return response()->json($response)->header('Content-Type', 'application/json');
    }
}
