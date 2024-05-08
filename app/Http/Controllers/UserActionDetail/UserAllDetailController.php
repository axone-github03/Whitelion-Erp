<?php

namespace App\Http\Controllers\UserActionDetail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserAllDetailController extends Controller
{
    function allContact(Request $request)
    {
        
        $response = successRes("All contact List");
        $data['contacts'] = getUserContactList($request->user_id)['data'];
        $data['lead_id'] = $request->user_id;
        $data['user']['id'] = $request->user_id;
        $response['view'] = view('user_action/detail_tab/detail_contact_tab', compact('data'))->render();
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function allFiles(Request $request)
    {

        $response = successRes("All files List");
        $data['files'] = getUserFileList($request->user_id)['data'];
        $data['lead_id'] = $request->user_id;
        $data['user']['id'] = $request->user_id;
        $response['view'] = view('user_action/detail_tab/detail_file_tab', compact('data'))->render();
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function allUpdates(Request $request)
    {

        $data = array();
        $data['updates'] = getUserNoteList($request->user_id)['data'];
        $data['user_id'] = $request->user_id;
        $response = successRes("All Update List");
        $response['view'] = view('user_action/detail_tab/detail_notes_tab', compact('data'))->render();
        return response()->json($response)->header('Content-Type', 'application/json');
    }


    function allOpenAction(Request $request)
    {
        $data = array();
        $data['calls'] = getUserAllOpenList($request->user_id)['call_data'];
        $data['meetings'] = getUserAllOpenList($request->user_id)['meeting_data'];
        $data['tasks'] = getUserAllOpenList($request->user_id)['task_data'];
        $data['user']['id'] = $request->user_id;
        $data['max_open_actions'] = getUserAllOpenList($request->user_id)['max_open_actions'];
        $response = successRes("All Open List");
        $response['view'] = view('user_action/detail_tab/detail_open_action_tab', compact('data'))->render();

        // $response['data'] = $data;
        return response()->json($response)->header('Content-Type', 'application/json');
    }

    function allCloseAction(Request $request)
    {
        $data = array();
        $data['calls_closed'] = getUserAllCloseList($request->user_id)['close_call_data'];
        $data['meetings_closed'] = getUserAllCloseList($request->user_id)['close_meeting_data'];
        $data['tasks_closed'] = getUserAllCloseList($request->user_id)['close_task_data'];
        $data['max_close_actions'] = getUserAllCloseList($request->user_id)['max_close_actions'];
        $data['user']['id'] = $request->user_id;
        $response = successRes("All Open List");
        $response['view'] = view('user_action/detail_tab/detail_close_action_tab', compact('data'))->render();

        // $response['data'] = $data;
        return response()->json($response)->header('Content-Type', 'application/json');
    }
}
