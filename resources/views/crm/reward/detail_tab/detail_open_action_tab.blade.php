<div class="card-header bg-transparent" style="border-bottom: 1px solid #f1e9e9 !important;border-radius: 0px;">
    <b>Open action </b>
    <div class="lds-spinner" id="open_action_loader" style="display: none;">
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
    </div>
    {{-- @if(isCreUser() == 0) 
    <div class="float-end" style="width: 115px;">
        <select class="form-select" id="add_action_type" name="add_action_type"
            onchange="changeAddActionType({{ $data['lead_id'] }})"
            style="height: 25px; font-size: 13px; padding: 2px 9px;">
            <option selected="" value="0">Add Action</option>
            <option value="1">Call</option>
            <option value="2">Meeting</option>
            <option value="3">Task</option>
        </select>
    </div>
    @endif --}}
</div>
<div class="card-body mb-0 text-center p-0">
    <table class="table table-sm table-bordered mb-0" border="0">
        <thead>
            <tr>
                <th class="px-2 col-4">Open Calls</th>
                <th style="background-color: #f3f3f3;" class="px-2 col-4">Open Meetings</th>
                <th class="px-2 col-4">Open Tasks</th>
            </tr>
        </thead>
        <tbody>

            @for ($i = 0; $i < $data['max_open_actions']; $i++)
                <tr>
                    @if (isset($data['calls'][$i]['contact_name']))
                        <td class="td_call py-0 pb-2 px-2" style="border: 1px solid #fff">
                            <div style="border-top: 2px solid #f1e9e9" class="pt-2">
                                    {{-- @if($data['calls'][$i]['is_reference'] == 1)
                                        @if(Auth::user()->type == 9)
                                            <div class="col-2 float-start d-inline-block mt-1">
                                                <i class='bx bx-check-circle text-success' style="font-size: 1.2rem;" onclick="viewAutoScheduleAction('{{ $data['calls'][$i]['id'] }}', 'call')"></i>
                                            </div>
                                        @else 
                                            <div class="col-2 float-start d-inline-block mt-1">
                                                <i class='bx bx-check-circle text-success' style="font-size: 1.2rem;" onclick="viewCall('{{ $data['calls'][$i]['id'] }}', 'open')"></i>
                                            </div>
                                        @endif
                                    @elseif($data['calls'][$i]['is_reference'] == 2)
                                        <div class="col-2 float-start d-inline-block mt-1 hover_tooltip">
                                            <i class="bx bx-check-circle text-danger me-2" style="font-size: 1.2rem;"></i>
                                            <span class="tooltiptext">{{ $data['calls'][$i]['tooltip_message'] }}</span>
                                        </div>
                                    @else
                                        <div class="col-2 float-start d-inline-block mt-1">
                                            <i class='bx bx-check-circle text-success' style="font-size: 1.2rem;" onclick="viewCall('{{ $data['calls'][$i]['id'] }}', 'open')"></i>
                                        </div>
                                    @endif --}}
                                <div class="col-10 d-inline-block text-start">
                                    <b class="mb-0" style="font-weight: bold;"><a href="javascript:void(0)"
                                            onclick="">{{ $data['calls'][$i]['contact_name'] }}
                                        </a></b>
                                    <p class="mb-0">{{ $data['calls'][$i]['purpose'] }}</p>
                                    <p class="mb-0">
                                        {{ $data['calls'][$i]['date'] }}{{ $data['calls'][$i]['time'] }}<i
                                            class='bx bxs-user ms-2'></i>
                                        {{ $data['calls'][$i]['first_name'] }}
                                        {{ $data['calls'][$i]['last_name'] }}</p>
                                </div>
                            </div>
                        </td>
                    @else
                        <td class="td_call py-0 pb-2 px-2" style="border: 1px solid #fff"></td>
                    @endif

                    @if (isset($data['meetings'][$i]['location']))
                        <td class="td_meeting py-0 pb-2 px-2"
                            style="background-color: #f3f3f3; border: 1px solid #f3f3f3">
                            <div style="border-top: 2px solid #b9b9b9" class="pt-2">
                                {{-- <div class="col-2 float-start d-inline-block mt-1">
                                    <i class='bx bx-check-circle text-success' style="font-size: 1.2rem;"
                                        onclick="viewMeeting({{ $data['meetings'][$i]['id'] }}, 'open')"></i>
                                </div> --}}
                                <div class="col-10 d-inline-block text-start">
                                    <b class="mb-0" style="font-weight: bold;"><a
                                            href="javascript:void(0)">{!! $data['meetings'][$i]['meeting_participant'] !!}</a></b>
                                    <p class="mb-0">{{ $data['meetings'][$i]['title_name'] }}</p>
                                    <p class="mb-0">{{ $data['meetings'][$i]['date'] }}
                                        {{ $data['meetings'][$i]['time'] }} <b
                                            class="text-primary">{{ $data['meetings'][$i]['location'] }}</b><i
                                            class='bx bxs-user mx-1'></i>{{ $data['meetings'][$i]['first_name'] }}
                                        {{ $data['meetings'][$i]['last_name'] }} </p>
                                </div>
                            </div>
                        </td>
                    @else
                        <td class="td_meeting py-0 pb-2 px-2"
                            style="background-color: #f3f3f3; border: 1px solid #f3f3f3"></td>
                    @endif

                    @if (isset($data['tasks'][$i]))
                        <td class="td_task py-0 pb-2 px-2" style="border: 1px solid #fff">
                            <div style="border-top: 2px solid #f1e9e9" class="pt-2">
                                    {{-- @if($data['tasks'][$i]['is_autogenerate'] == 1)
                                        @if($data['tasks'][$i]['assign_to'] == Auth::user()->id)
                                            @if(Auth::user()->type == 9)
                                                <div class="col-2 float-start d-inline-block mt-1">
                                                    <i class='bx bx-check-circle text-success' style="font-size: 1.2rem;" onclick="viewAutoScheduleAction({{ $data['tasks'][$i]['id'] }}, 'task')"></i>
                                                </div>
                                            @else 
                                                <div class="col-2 float-start d-inline-block mt-1">
                                                    <i class='bx bx-check-circle text-success' style="font-size: 1.2rem;" onclick="viewTask({{ $data['tasks'][$i]['id'] }}, 'open')"></i>
                                                </div>
                                            @endif
                                        @else
                                            <div class="col-2 float-start d-inline-block mt-1 hover_tooltip">
                                                <i class="bx bx-check-circle text-danger me-2" style="font-size: 1.2rem;"></i>
                                                <span class="tooltiptext">{{ $data['tasks'][$i]['tooltip_message'] }}</span>
                                            </div>
                                        @endif
                                    @else
                                        <i class='bx bx-check-circle text-success' style="font-size: 1.2rem;" onclick="viewTask({{ $data['tasks'][$i]['id'] }}, 'open')"></i>
                                    @endif --}}
                                <div class="col-10 d-inline-block text-start">
                                    <p class="mb-0" style="font-weight: bold;"><a href="javascript:void(0)">{{ $data['tasks'][$i]['task'] }} </a>
                                    <p class="mb-0"> {{ $data['tasks'][$i]['date'] }}</p>
                                    <p class="mb-0"><i class='bx bxs-user me-1'></i>{{ $data['tasks'][$i]['task_owner'] }}</p>
                                </div>
                            </div>
                        </td>
                    @else
                        <td class="td_task py-0 pb-2 px-2" style="border: 1px solid #fff"></td>
                    @endif



                </tr>
            @endfor

        </tbody>
    </table>
</div>
