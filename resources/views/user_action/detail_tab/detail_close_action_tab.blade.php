<div class="card-header bg-transparent border-bottom"
    style="border-bottom: 1px solid #f1e9e9 !important;border-radius: 0px;">
    <b>Closed action</b><i id="close_action_loader" class="bx bx-loader bx-spin font-size-16 align-middle me-2" style="display: none;"></i>

</div>
<div class="card-body mb-2 text-center p-0">
    <table class="table table-sm mb-0 table-bordered" border="0">
        <thead>
            <tr>
                <th class="px-2 col-4">Closed Calls</th>
                <th class="px-2 col-4" style="background-color: #f3f3f3;">Closed Meetings</th>
                <th class="px-2 col-4">Closed Tasks</th>
            </tr>
        </thead>
        <tbody>
            @for ($i = 0; $i < $data['max_close_actions']; $i++)
                <tr>
                    @if (isset($data['calls_closed'][$i]['contact_name']))
                        <td class="td_call py-0 pb-2 px-2" style="border: 1px solid #fff">
                            <div style="border-top: 2px solid #f1e9e9" class="pt-2">
                                {{-- <div class="col-2 float-start d-inline-block mt-1">
                                                <i class='bx bx-check-circle text-success' style="font-size: 1.2rem;"></i>
                                            </div> --}}
                                <div class="col-10 d-inline-block text-start">
                                    <b class="mb-0" style="font-weight: bold;"><a href="javascript:void(0)"
                                            onclick="viewCall({{ $data['calls_closed'][$i]['id'] }}, 'close')">{{ $data['calls_closed'][$i]['contact_name'] }}
                                        </a></b>
                                    <p class="mb-0">{{ $data['calls_closed'][$i]['purpose'] }}</p>
                                    <p class="mb-0">{{ $data['calls_closed'][$i]['date'] }}
                                        {{ $data['calls_closed'][$i]['time'] }} <i class='bx bxs-user ms-2'></i>
                                        {{ $data['calls_closed'][$i]['first_name'] }}
                                        {{ $data['calls_closed'][$i]['last_name'] }}</p>
                                </div>
                            </div>
                        </td>
                    @else
                        <td class="td_call py-0 pb-2 px-2" style="border: 1px solid #fff"></td>
                    @endif



                    @if (isset($data['meetings_closed'][$i]['location']))
                        <td class="td_meeting py-0 pb-2 px-2"
                            style="background-color: #f3f3f3; border: 1px solid #f3f3f3">
                            <div style="border-top: 2px solid #b9b9b9" class="pt-2">
                                {{-- <div class="col-2 float-start d-inline-block mt-1">
                                                <i class='bx bx-check-circle text-success' style="font-size: 1.2rem;"></i>
                                            </div> --}}
                                <div class="col-10 d-inline-block text-start">
                                    <b class="mb-0" style="font-weight: bold;"><a href="javascript:void(0)"
                                            onclick="viewMeeting({{ $data['meetings_closed'][$i]['id'] }}, 'close')">{!! $data['meetings_closed'][$i]['meeting_participant'] !!}</a></b>
                                    <p class="mb-0">
                                        {{ $data['meetings_closed'][$i]['title_name'] }}</p>
                                    <p class="mb-0">{{ $data['meetings_closed'][$i]['date'] }}
                                        {{ $data['meetings_closed'][$i]['time'] }} <b
                                            class="text-primary">{{ $data['meetings_closed'][$i]['location'] }}</b><i
                                            class='bx bxs-user mx-1'></i>{{ $data['meetings_closed'][$i]['first_name'] }}
                                        {{ $data['meetings_closed'][$i]['last_name'] }} </p>
                                </div>
                            </div>
                        </td>
                    @else
                        <td class="td_meeting py-0 pb-2 px-2"
                            style="background-color: #f3f3f3; border: 1px solid #f3f3f3"></td>
                    @endif

                    @if (isset($data['tasks_closed'][$i]['task']))
                        <td class="td_task py-0 pb-2 px-2" style="border: 1px solid #fff">
                            <div style="border-top: 2px solid #f1e9e9" class="pt-2">
                                {{-- <div class="col-2 float-start d-inline-block mt-1">
                                            <i class='bx bx-check-circle text-success' style="font-size: 1.2rem;"></i>
                                        </div> --}}
                                <div class="col-10 d-inline-block text-start">
                                    <p class="mb-0" style="font-weight: bold;"><a href="javascript:void(0)"
                                            onclick="viewTask({{ $data['tasks_closed'][$i]['id'] }}, 'close')">
                                            {{ $data['tasks_closed'][$i]['task'] }} </a>
                                        {{-- </a><span style="font-size: 12px;color: #5a5a5a94;" class="ms-5"><i class='bx bx-edit-alt'></i><i class='bx bx-trash ms-2'></i></span></p> --}}
                                    <p class="mb-0"> {{ $data['tasks_closed'][$i]['date'] }}</p>
                                    <p class="mb-0"><i
                                            class='bx bxs-user me-1'></i>{{ $data['tasks_closed'][$i]['task_owner'] }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        {{-- <td class="td_task">

                                        <a href="javascript:void(0)" onclick="viewTask({{ $data['tasks_closed'][$i]['id'] }})"> {{ $data['tasks_closed'][$i]['task'] }}</a>

                                    </td> --}}
                    @else
                        <td class="td_task py-0 pb-2 px-2" style="border: 1px solid #fff"></td>
                    @endif



                </tr>
            @endfor

        </tbody>
    </table>
</div>
