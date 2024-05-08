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
use App\Models\QuotRequestDetail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\QuotRequest;
use App\Models\Wltrn_QuotItemdetail;
use Illuminate\Support\Facades\File;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

// use Illuminate\Http\Request;
class QuotationRequestController extends Controller
{
    public function quotRoomWiseBoardList(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'quot_id' => ['required'],
            'quotgroup_id' => ['required'],
        ]);

        if ($validator->fails()) {
            $response = errorRes($validator->errors()->first());
            $response['data'] = $validator->errors();
        } else {
            $room_columns = ['wltrn_quot_itemdetails.quot_id', 'wltrn_quot_itemdetails.quotgroup_id', 'wltrn_quot_itemdetails.room_no', 'wltrn_quot_itemdetails.room_name'];

            $room_query = Wltrn_QuotItemdetail::query();
            $room_query->select($room_columns);
            $room_query->where('wltrn_quot_itemdetails.quot_id', $request->quot_id);
            $room_query->where('wltrn_quot_itemdetails.quotgroup_id', $request->quotgroup_id);
            $room_query->groupBy($room_columns);
            $room_query->orderBy('wltrn_quot_itemdetails.room_no', 'ASC');
            $roomlist = $room_query->get();
            foreach ($roomlist as $key => $value) {
                $roomlist[$key]['isRoom'] = false;
                $board_columns = ['wltrn_quot_itemdetails.board_no', 'wltrn_quot_itemdetails.board_name', 'wltrn_quot_itemdetails.item_type'];

                $board_query = Wltrn_QuotItemdetail::query();
                $board_query->select($board_columns);
                $board_query->selectRaw('CONCAT(board_name," (", item_type,")") AS board_title');
                $board_query->where('wltrn_quot_itemdetails.quot_id', $value->quot_id);
                $board_query->where('wltrn_quot_itemdetails.quotgroup_id', $value->quotgroup_id);
                $board_query->where('wltrn_quot_itemdetails.room_no', $value->room_no);
                $board_query->where('wltrn_quot_itemdetails.board_no', '<>', 0);
                $board_query->groupBy($board_columns);
                $board_query->orderBy('wltrn_quot_itemdetails.board_no', 'ASC');
                $board_list = $board_query->get();
                foreach ($board_list as $board_key => $board_key) {
                    $board_list[$board_key]['isBoard'] = false;
                }
                $roomlist[$key]['board'] = $board_list;
            }
            $response = successRes('Successfully get list of board');
            $response['data'] = $roomlist;
        }
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }

    public function saveQuotConversationRequest(Request $request)
    {
        $validator = Validator::make($request->input(), [
            'body' => ['required'],
        ]);

        if ($validator->fails()) {
            $response = errorRes($validator->errors()->first());
            $response['data'] = $validator->errors();
        } else {
            $request_data = $request->all()['body'];

            $group_id = 0;
            $assign_person = 12;
            $QuotRequest_id = 0;
            $max_group_id = DB::table('quotation_request')->max('group_id');
            if ($max_group_id) {
                $group_id = $max_group_id + 1;
            } else {
                $group_id = 1;
            }

            foreach ($request_data as $room_key => $room_value) {
                if ($room_key == 0) {
                    $Quot_Request = new QuotRequest();
                    $Quot_Request->group_id = $group_id;
                    $Quot_Request->quot_id = $room_value['quot_id'];
                    $Quot_Request->quotgroup_id = $room_value['quotgroup_id'];
                    $Quot_Request->subgroup_id = 0;
                    $Quot_Request->discount = 0;
                    $Quot_Request->deal_id = Wltrn_Quotation::find($room_value['quot_id'])->inquiry_id;
                    $Quot_Request->title = 'Quotation Conversation';
                    $Quot_Request->type = 'CONVERSATION';
                    $Quot_Request->assign_to = $assign_person;
                    $Quot_Request->status = 0;
                    $Quot_Request->remark = $request->remark;
                    $Quot_Request->entryby = Auth::user()->id;
                    $Quot_Request->entryip = $request->ip();
                    $Quot_Request->save();

                    if($Quot_Request){

                        $QuotRequest_id = $Quot_Request->id;
                        $update_Quotation = Wltrn_Quotation::find($room_value['quot_id']);
                        $update_Quotation->status = 2;
                        $update_Quotation->save();
                    }

                    // if($room_value['isRoom']){
                        foreach ($room_value['board'] as $board_key => $board_value) {
                            if($board_value['isBoard']){
                                $QuotRequestDetail = new QuotRequestDetail();
                                $QuotRequestDetail->quot_req_id = $QuotRequest_id;
                                $QuotRequestDetail->group_id = $group_id;
                                $QuotRequestDetail->quot_id = $room_value['quot_id'];
                                $QuotRequestDetail->quot_room_no = $room_value['room_no'];
                                $QuotRequestDetail->quot_board_no = $board_value['board_no'];
                                $QuotRequestDetail->assign_to = $assign_person;
                                $QuotRequestDetail->remark = $request->remark;
                                $QuotRequestDetail->status = 0;
                                $QuotRequestDetail->entryby = Auth::user()->id;
                                $QuotRequestDetail->entryip = $request->ip();
                                $QuotRequestDetail->save();
                            }
                        }
                    // }

                }else{
                    // if($room_value['isRoom']){
                        foreach ($room_value['board'] as $board_key => $board_value) {
                            if($board_value['isBoard']){
                                $QuotRequestDetail = new QuotRequestDetail();
                                $QuotRequestDetail->quot_req_id = $QuotRequest_id;
                                $QuotRequestDetail->group_id = $group_id;
                                $QuotRequestDetail->quot_id = $room_value['quot_id'];
                                $QuotRequestDetail->quot_room_no = $room_value['room_no'];
                                $QuotRequestDetail->quot_board_no = $board_value['board_no'];
                                $QuotRequestDetail->assign_to = $assign_person;
                                $QuotRequestDetail->remark = $request->remark;
                                $QuotRequestDetail->status = 0;
                                $QuotRequestDetail->entryby = Auth::user()->id;
                                $QuotRequestDetail->entryip = $request->ip();
                                $QuotRequestDetail->save();
                            }
                        }
                    // }
                }
            }
            
            $response = successRes('Successfully quotation request placed');
            $response['data'] = [];
        }
        return response()
            ->json($response)
            ->header('Content-Type', 'application/json');
    }
}
