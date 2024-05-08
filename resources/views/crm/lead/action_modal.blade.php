<div class="modal fade" id="modalTask" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalTaskLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-s" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTaskLabel">Schedule Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form enctype="multipart/form-data" id="formLeadTask" action="{{ route('crm.lead.task.save') }}"
                method="POST" class="needs-validation" novalidate>
                <div class="modal-body">
                    @csrf
                    <div class="col-md-12 text-center loadingcls">
                        <button type="button" class="btn btn-light waves-effect">
                            <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                        </button>
                    </div>
                    <input type="hidden" name="lead_task_lead_id" id="lead_task_lead_id">
                    <input type="hidden" name="lead_task_id" id="lead_task_id">
                    <input type="hidden" name="lead_task_auto_generate" id="lead_task_auto_generate" value="0">

                    <div class="form-group row align-items-center mb-1" id="lead_task_assign_to_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Task Owner</label>
                        <div class="col-sm-8 ps-0" id="pointer_event_assign_to">
                            <select class="form-control select2-ajax" id="lead_task_assign_to"
                                name="lead_task_assign_to" required></select>
                            <div class="invalid-feedback">Please select assign</div>
                        </div>
                    </div>

                    <div class="form-group row align-items-center mb-1" id="lead_task_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Task</label>
                        <div class="col-sm-8 ps-0">
                            <input type="text" class="form-control" id="lead_task_task" name="lead_task_task"
                                placeholder="Task" value="" required>
                        </div>
                    </div>

                    <div class="form-group row align-items-center mb-1" id="lead_task_due_date_time_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Due Date </label>
                        <div class="col-sm-4 ps-0">
                            <div class="input-group align-items-center" id="div_itemprice_effective_date">
                                <input type="text" class="form-control" value="{{ date('d-m-Y') }}"
                                    placeholder="dd-mm-yyyy" data-date-format="dd-mm-yyyy"
                                    data-date-container='#div_itemprice_effective_date' data-provide="datepicker"
                                    data-date-autoclose="true" required name="lead_task_due_date"
                                    id="lead_task_due_date">
                                <div class="position-absolute end-0 z-index-1 me-2" for="lead_task_due_date">
                                    <i class='bx bx-calendar bx-flashing'></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 ps-0">
                            <select class="form-control" id="lead_task_due_time" name="lead_task_due_time" required
                                style="border: 1px solid #ced4da;border-radius: 0.25rem;">
                                @foreach (getTimeSlot() as $timeSlot)
                                    <option value="{{ $timeSlot }}">{{ $timeSlot }} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>


                    <div class="form-group row align-items-center mb-1" id="lead_task_reminder_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Reminder
                            Notification</label>
                        <div class="col-sm-8 ps-0">
                            <select class="form-control select2-ajax" id="lead_task_reminder_date_time"
                                name="lead_task_reminder_date_time" required=""></select>
                            <div class="invalid-feedback">Please select Reminder Time</div>

                        </div>
                    </div>

                    <div class="form-group row align-items-center mb-1" id="lead_task_description_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Task Note</label>
                        <div class="col-sm-8 ps-0">
                            <textarea type="text" class="form-control" id="lead_task_description" name="lead_task_description"
                                placeholder="Task Note" value="" required rows="1"></textarea>
                        </div>
                    </div>

                    <div class="form-group row align-items-center mb-1 d-none" id="lead_closing_note_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Closing
                            Note</label>
                        <div class="col-sm-8 ps-0">
                            <textarea type="text" class="form-control" id="lead_task_closing_note" name="lead_task_closing_note"
                                placeholder="Closing Note" value="" required rows="1"></textarea>
                        </div>
                    </div>

                    <div class="form-group row align-items-center mb-1 d-none" id="lead_task_outcome_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Task
                            outcome</label>
                        <div class="col-sm-8 ps-0">
                            <select class="form-control select2-ajax" id="lead_task_task_outcome"
                                name="lead_task_task_outcome" required=""></select>
                            <div class="invalid-feedback">Please select outcome type</div>
                        </div>
                    </div>

                    @if(isset($data['current_status']) && $data['current_status'] != 103)
                        <div class="form-group row align-items-center mb-1 d-none" id="lead_status_div">
                            <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Lead Status</label>
                            <div class="col-sm-8 ps-0">
                                <select class="form-control select2-ajax" id="lead_task_status" name="lead_task_status"
                                    required=""></select>
                            </div>
                        </div>
                    @endif

                    @if(Auth::user()->type == 11)
                        <div class="d-none" id="task_add_info_div">
                            <div class="form-group row  align-items-center mb-1">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Architect</label>
                                <div class="col-sm-8 ps-0">
                                    <input type="text" class="form-control" id="lead_task_add_info_arc" name="lead_task_add_info_arc" placeholder="Architect Name" value="">
                                </div>
                            </div>

                            <div class="form-group row  align-items-center mb-1">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Electrician</label>
                                <div class="col-sm-8 ps-0">
                                    <input type="text" class="form-control" id="lead_task_add_info_ele" name="lead_task_add_info_ele" placeholder="Electrician Name" value="">
                                </div>
                            </div>

                            <div class="form-group row  align-items-center mb-1">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Additional Info</label>
                                <div class="col-sm-8 ps-0">
                                    <select class="form-control select2-ajax" id="lead_task_add_info" name="lead_task_add_info"></select>
                                </div>
                            </div>

                            <div class="form-group row  align-items-center mb-1 d-none" id="lead_task_add_info_text_div">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Additional Text</label>
                                <div class="col-sm-8 ps-0">
                                    <input type="text" class="form-control" id="lead_task_add_info_text" name="lead_task_add_info_text" placeholder="Additional Text" value="">
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <input type="hidden" name="lead_task_move_to_close" id="lead_task_move_to_close" />
                </div>
                <div class="modal-footer">
                    <div id="taskfooter1">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button id="lead_task_move_to_close_btn" type="button" class="btn btn-warning">Close
                            Task</button>
                        <button type="submit" class="btn btn-primary save-btn">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalMeeting" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="modalMeetingLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMeetingLabel">Set up Meeting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <div class="row justify-content-evenly">
                    <div class="col-3 py-3 bg-white" style="max-height: 450px;overflow-y: auto;overflow-x: hidden;border-radius: 10px;" id="meeting_date_schedule">
                            <table id="ScheduleTable" class="table align-middle mb-0 w-100 dataTable no-footer">
                                <thead class="d-none">
                                    <tr>
                                        <td></td>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                    </div>
                    <div class="col-12 bg-white p-2" style="border-radius: 10px;" id="meeting_form">
                        <form enctype="multipart/form-data" id="formLeadMeeting" action="{{ route('crm.lead.meeting.save') }}" method="POST" class="needs-validation" novalidate>
                            @csrf
                            
                            <div class="col-md-12 text-center loadingcls">
                                <button type="button" class="btn btn-light waves-effect">
                                    <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i>Loading
                                </button>
                            </div>

                            <input type="hidden" name="lead_meeting_lead_id" id="lead_meeting_lead_id">
                            <input type="hidden" name="lead_meeting_id" id="lead_meeting_id">

                            <div class="form-group row align-items-center mb-1" id="lead_meeting_title_div">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Title</label>
                                <div class="col-sm-8 ps-0" id="pointer_event_meeting_title">
                                    <select class="form-control select2-ajax" id="lead_meeting_title_id"
                                        name="lead_meeting_title_id" required=""></select>
                                    <div class="invalid-feedback">Please select title </div>
                                </div>
                            </div>

                            <div class="form-group row align-items-center mb-1" id="lead_meeting_type_div">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Type</label>
                                <div class="col-sm-8 ps-0" id="pointer_event_meeting_type">
                                    <select class="form-control select2-ajax" id="lead_meeting_type_id"
                                        name="lead_meeting_type_id" required=""></select>
                                    <div class="invalid-feedback">Please select type</div>
                                </div>
                            </div>

                            <div class="form-group row align-items-center mb-1" id="lead_meeting_participants_div">
                                <label for="horizontal-firstname-input"
                                    class="col-sm-4 col-form-label">Participants</label>
                                <div class="col-sm-8 ps-0" id="pointer_event_meeting_participants">
                                    <select multiple="multiple" class="form-control select2-ajax select2-multiple"
                                        id="lead_meeting_participants" name="lead_meeting_participants[]"></select>
                                    <div class="invalid-feedback">Please select title </div>
                                </div>
                            </div>

                            <div class="form-group row align-items-center mb-1" id="lead_meeting_meeting_date_time_div">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Start Date & Time</label>
                                <div class="col-sm-4 ps-0">
                                    <div class="input-group align-items-center" id="lead_meeting_effective_date">
                                        <input type="text" class="form-control" value="{{ date('d-m-Y') }}"
                                            placeholder="dd-mm-yyyy" data-date-format="dd-mm-yyyy"
                                            data-date-container='#lead_meeting_effective_date'
                                            data-provide="datepicker" data-date-autoclose="true" required
                                            name="lead_meeting_date" id="lead_meeting_date">
                                        <div class="position-absolute end-0 z-index-1 me-2" for="lead_task_due_date">
                                            <i class='bx bx-calendar bx-flashing'></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-4 ps-0">
                                    <select class="form-control" id="lead_meeting_time" name="lead_meeting_time"
                                        required style="border: 1px solid #ced4da;border-radius: 0.25rem;">
                                        @foreach (getTimeSlot() as $timeSlot)
                                            <option value="{{ $timeSlot }}">{{ $timeSlot }} </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class="form-group row align-items-center mb-1" id="lead_meeting_interval_time_div">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Meeting Interval Time</label>
                                <div class="col-sm-8 ps-0">
                                    <select class="form-control" id="lead_meeting_interval_time" name="lead_meeting_interval_time"
                                        required style="border: 1px solid #ced4da;border-radius: 0.25rem;">
                                        @foreach (getIntervalTime() as $intervalTime)
                                            <option value="{{ $intervalTime['id'] }}">{{ $intervalTime['name'] }} </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        

                            <div class="form-group row align-items-center mb-1" id="lead_meeting_is_notification_div">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Reminder
                                    Notification</label>
                                <div class="col-sm-8 ps-0">
                                    <select class="form-control select2-ajax" id="lead_meeting_reminder_date_time"
                                        name="lead_meeting_reminder_date_time" required=""></select>
                                    <div class="invalid-feedback">Please select reminder Time</div>
                                </div>


                            </div>

                            <div class="form-group row align-items-center mb-1" id="lead_meeting_location_div">
                                <label for="horizontal-firstname-input"
                                    class="col-sm-4 col-form-label">Location</label>
                                <div class="col-sm-8 ps-0">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="lead_meeting_location"
                                            name="lead_meeting_location" placeholder="Location Name" value=""
                                            required>
                                        <div class="input-group-text">
                                            <div class="form-check form-switch me-1">
                                                <input class="form-check-input" type="checkbox"
                                                    id="flexSwitchCheckDefault">
                                            </div>
                                            <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                                                width="25" height="25" viewBox="0 0 48 48">
                                                <path fill="#5c6bc0"
                                                    d="M41.5 13A3.5 3.5 0 1 0 41.5 20 3.5 3.5 0 1 0 41.5 13zM4 40l23 4V4L4 8V40z">
                                                </path>
                                                <path fill="#fff"
                                                    d="M21 16.27L21 19 17.01 19.18 16.99 31.04 14.01 30.95 14.01 19.29 10 19.45 10 16.94z">
                                                </path>
                                                <path fill="#5c6bc0"
                                                    d="M36 14c0 2.21-1.79 4-4 4-1.2 0-2.27-.53-3-1.36v-5.28c.73-.83 1.8-1.36 3-1.36C34.21 10 36 11.79 36 14zM38 23v11c0 0 1.567 0 3.5 0 1.762 0 3.205-1.306 3.45-3H45v-8H38zM29 20v17c0 0 1.567 0 3.5 0 1.762 0 3.205-1.306 3.45-3H36V20H29z">
                                                </path>
                                            </svg>
                                            <div class="ms-1">
                                                Meeting
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row align-items-center mb-1" id="lead_meeting_note_div">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label"
                                    id="lead_meeting_description_label">Meeting Notes</label>
                                <div class="col-sm-8 ps-0">
                                    <textarea type="text" class="form-control" id="lead_meeting_description" name="lead_meeting_description"
                                        placeholder="Meeting Notes" value="" required rows="1"></textarea>
                                </div>
                            </div>

                            <div class="form-group row align-items-center mb-1 d-none"
                                id="lead_meeting_closing_note_div">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Closing
                                    Notes</label>
                                <div class="col-sm-8 ps-0">
                                    <textarea type="text" class="form-control" id="close_meeting_note" name="close_meeting_note"
                                        placeholder="Description" value="" required rows="1"></textarea>
                                </div>
                            </div>

                            <div class="form-group row align-items-center mb-1 d-none" id="lead_meeting_outcome_div">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Meeting
                                    outcome</label>
                                <div class="col-sm-8 ps-0">
                                    <select class="form-control select2-ajax" id="lead_meeting_meeting_outcome"
                                        name="lead_meeting_meeting_outcome" required=""></select>
                                    <div class="invalid-feedback">Please select outcome type</div>
                                </div>
                            </div>

                            @if(isset($data['current_status']) && $data['current_status'] != 103)
                                <div class="form-group row align-items-center mb-1 d-none" id="lead_meeting_status_div">
                                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Lead
                                        Status</label>
                                    <div class="col-sm-8 ps-0">
                                        <select class="form-control select2-ajax" id="lead_meeting_status"
                                            name="lead_meeting_status" required=""></select>
                                    </div>
                                </div>
                            @endif

                            <input type="hidden" id="lead_meeting_move_to_close"
                                name="lead_meeting_move_to_close" />
                            <div class="modal-footer">
                                <div id="meetingFooter1">
                                    <button type="button" class="btn btn-light"
                                        data-bs-dismiss="modal">Close</button>
                                    <button id="lead_meeting_move_to_close_btn" type="button"
                                        class="btn btn-warning">Close
                                        Meeting</button>
                                    <button type="submit" class="btn btn-primary save-btn">Save</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCall" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="modalCallLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-s" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCallLabel">Call</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="call_close_cross_btn"></button>
            </div>
            <form id="formLeadCall" action="{{ route('crm.lead.call.save') }}" method="POST"
                class="needs-validation" novalidate>
                <div class="modal-body p-0 m-4">
                    @csrf
                    <div class="col-md-12 text-center loadingcls">
                        <button type="button" class="btn btn-light waves-effect">
                            <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                        </button>
                    </div>
                    <input type="hidden" name="lead_call_lead_id" id="lead_call_lead_id">
                    <input type="hidden" name="lead_call_id" id="lead_call_id">
                    <input type="hidden" name="lead_is_auto_call" id="lead_is_auto_call" value="0">
                    <div class="form-group row  align-items-center mb-1" id="lead_call_type_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Type</label>
                        <div class="col-sm-8 ps-0" id="pointer_event_call_type">
                            <select class="form-control select2-ajax" id="lead_call_type_id" name="lead_call_type_id"
                                required=""></select>
                            <div class="invalid-feedback">Please select type</div>
                        </div>
                    </div>

                    <div class="form-group row  align-items-center mb-1" id="lead_call_contact_name_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Contact Name</label>
                        <div class="col-sm-8 ps-0" id="pointer_event_call_contact_name">
                            <select class="form-control select2-ajax" id="lead_call_contact_name" name="lead_call_contact_name" required=""></select>
                            <div class="invalid-feedback">Please select Contact</div>
                        </div>
                    </div>
                    @if(Auth::user()->type == 9)
                        <div class="form-group row  align-items-center mb-1" id="lead_call_assign_user_div">
                            <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Assign User</label>
                            <input type="hidden" name="hidden_user_type" id="hidden_user_type" value="{{Auth::user()->type}}">
                            <input type="hidden" name="hidden_user_id" id="hidden_user_id" value="{{Auth::user()->id}}">
                            <div class="col-sm-8 ps-0" id="pointer_event_call_assign_user">
                                <select class="form-control select2-ajax" id="lead_call_assign_user" name="lead_call_assign_user"></select>
                                <div class="invalid-feedback">Please assign user</div>
                            </div>
                        </div>
                    @endif
                    <div class="form-group row  align-items-center mb-1" id="lead_call_call_schedule_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Call Schedule</label>
                        <div class="col-sm-4 ps-0">
                            <div class="input-group align-items-center" id="lead_call_schedule_effective_date">
                                <input type="text" class="form-control" value="{{ date('d-m-Y') }}"
                                    placeholder="dd-mm-yyyy" data-date-format="dd-mm-yyyy"
                                    data-date-container='#lead_call_schedule_effective_date' data-provide="datepicker"
                                    data-date-autoclose="true" required name="lead_call_schedule_date"
                                    id="lead_call_schedule_date">
                                <div class="position-absolute end-0 z-index-1 me-2" for="lead_task_due_date">
                                    <i class='bx bx-calendar bx-flashing'></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 ps-0">
                            <select class="form-control" id="lead_call_schedule_time" name="lead_call_schedule_time"
                                required style="border: 1px solid #ced4da;border-radius: 0.25rem;">
                                @foreach (getTimeSlot() as $timeSlot)
                                    <option value="{{ $timeSlot }}">{{ $timeSlot }} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row  align-items-center mb-1" id="lead_call_reminder_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Reminder
                            Notification</label>
                        <div class="col-sm-8 ps-0">
                            <select class="form-control select2-ajax" id="lead_call_reminder_date_time"
                                name="lead_call_reminder_date_time" required=""></select>
                            <div class="invalid-feedback">Please select reminder Time</div>

                        </div>

                    </div>

                    <div class="form-group row  align-items-center mb-1" id="lead_call_purpose_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Call Purpose</label>
                        <div class="col-sm-8 ps-0">
                            <input type="text" class="form-control" id="lead_call_call_purpose"
                                name="lead_call_purpose" placeholder="Call Purpose" value="" required>
                        </div>
                    </div>

                    <div class="form-group row  align-items-center mb-1" id="lead_call_notes_div">
                        <label for="horizontal-firstname-input" id="call_notes_label"
                            class="col-sm-4 col-form-label">Call Notes</label>
                        <div class="col-sm-8 ps-0">
                            <textarea type="text" class="form-control" id="lead_call_call_description" name="lead_call_description"
                                placeholder="Call Notes" value="" rows="1"></textarea>
                        </div>
                    </div>

                    <div class="form-group row  align-items-center mb-1 d-none" id="lead_call_closing_note_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Closing Notes</label>
                        <div class="col-sm-8 ps-0">
                            <textarea type="text" class="form-control" id="lead_call_closing_note" name="lead_call_closing_note" placeholder="Closing Notes" value="" rows="1"></textarea>
                        </div>
                    </div>

                    <div class="form-group row  align-items-center mb-1 d-none" id="lead_call_outcome_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Call outcome</label>
                        <div class="col-sm-8 ps-0">
                            <select class="form-control select2-ajax" id="lead_call_call_outcome" name="lead_call_call_outcome" required=""></select>
                            <div class="invalid-feedback">Please select outcome type</div>
                        </div>
                    </div>
                    @if(isset($data['current_status']) && $data['current_status'] != 103)
                        <div class="form-group row  align-items-center mb-1 d-none" id="lead_call_status_div">
                            <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Lead Status</label>
                            <div class="col-sm-8 ps-0">
                                <select class="form-control select2-ajax" id="lead_call_status" name="lead_call_status"
                                    required=""></select>
                            </div>
                        </div>
                    @endif

                    @if(Auth::user()->type == 9)
                        <div class="d-none" id="add_info_div">
                            <div class="form-group row  align-items-center mb-1">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Architect</label>
                                <div class="col-sm-8 ps-0">
                                    <input type="text" class="form-control" id="lead_call_add_info_arc" name="lead_call_add_info_arc" placeholder="Architect Name" value="">
                                </div>
                            </div>

                            <div class="form-group row  align-items-center mb-1">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Electrician</label>
                                <div class="col-sm-8 ps-0">
                                    <input type="text" class="form-control" id="lead_call_add_info_ele" name="lead_call_add_info_ele" placeholder="Electrician Name" value="">
                                </div>
                            </div>

                            <div class="form-group row  align-items-center mb-1">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Additional Info</label>
                                <div class="col-sm-8 ps-0">
                                    <select class="form-control select2-ajax" id="lead_call_add_info" name="lead_call_add_info"></select>
                                </div>
                            </div>

                            <div class="form-group row  align-items-center mb-1 d-none" id="lead_call_add_info_text_div">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Additional Text</label>
                                <div class="col-sm-8 ps-0">
                                    <input type="text" class="form-control" id="lead_call_add_info_text" name="lead_call_add_info_text" placeholder="Additional Text" value="">
                                </div>
                            </div>
                        </div>
                    @endif

                    <div id="call_received_but_reschedule_div" class="d-none form-group row  align-items-center mb-1">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Call Re-Schedule</label>
                        <div class="col-sm-4 ps-0">
                            <div class="input-group align-items-center" id="lead_call_re_schedule_effective_date">
                                <input type="text" class="form-control" value="{{ date('d-m-Y') }}"
                                    placeholder="dd-mm-yyyy" data-date-format="dd-mm-yyyy"
                                    data-date-container='#lead_call_re_schedule_effective_date' data-provide="datepicker"
                                    data-date-autoclose="true" required name="lead_re_call_schedule_date"
                                    id="lead_re_call_schedule_date">
                                <div class="position-absolute end-0 z-index-1 me-2" for="lead_re_call_schedule_date">
                                    <i class='bx bx-calendar bx-flashing'></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 ps-0">
                            <select class="form-control" id="lead_call_re_schedule_time" name="lead_call_re_schedule_time" required style="border: 1px solid #ced4da;border-radius: 0.25rem;">
                                @foreach (getTimeSlot() as $timeSlot)
                                    <option value="{{ $timeSlot }}">{{ $timeSlot }} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <input type="hidden" name="lead_call_move_to_close" id="lead_call_move_to_close" />
                    <div class="modal-footer">
                        <div id="callFooter1" class="callFooter">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal" id="call_close_btn">Close</button>
                            <button type="button" id="lead_call_move_to_close_btn" class="btn btn-warning">Close
                                Call</button>
                            <button type="button" class="btn btn-primary save-btn">Save</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAutoScheduleCall" data-bs-keyboard="false" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="modalAutoScheduleCallLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAutoScheduleCallLabel">Call</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="call_close_cross_btn"></button>
            </div>
            <div class="row">
                <div class="col-6">
                    <form id="formAutogenerateAction" action="{{ route('crm.lead.call.save') }}" method="POST" class="needs-validation" novalidate>
                        <div class="modal-body p-0 m-4">
                            @csrf
                            <div for="" style="background-color: #eff2f7;font-weight: bold;" class="col-12 text-center mb-1">Task</div>
                            <div class="col-md-12 text-center loadingcls">
                                <button type="button" class="btn btn-light waves-effect">
                                    <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                                </button>
                            </div>
                            <input type="hidden" name="lead_auto_call_lead_id" id="lead_auto_call_lead_id">
                            <input type="hidden" name="lead_auto_call_id" id="lead_auto_call_id">
                            <input type="hidden" name="lead_auto_task_id" id="lead_auto_task_id">
                            <input type="hidden" name="lead_is_auto_call" id="lead_is_auto_call" value="1">
                            <input type="hidden" name="lead_auto_call_move_to_close" id="lead_auto_call_move_to_close" value="0">

                            <div class="form-group row align-items-center mb-1" id="lead_auto_task_assign_to_div">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Task Owner</label>
                                <div class="col-sm-8 ps-0">
                                    <input type="text" class="form-control" id="lead_auto_task_assign_to" name="lead_auto_task_assign_to" readonly>
                                </div>
                            </div>

                            <div class="form-group row align-items-center mb-1" id="lead_auto_task_div">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Task</label>
                                <div class="col-sm-8 ps-0">
                                    <input type="text" class="form-control" id="lead_auto_task" name="lead_auto_task" readonly>
                                </div>
                            </div>
                            <div class="form-group row align-items-center mb-1" id="lead_auto_phone_number_div">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Mobile No.</label>
                                <div class="col-sm-8 ps-0">
                                    <input type="text" class="form-control" id="lead_auto_phone_number" name="lead_auto_phone_number" readonly>
                                </div>
                            </div>

                            <div class="form-group row align-items-center mb-1" id="lead_auto_task_due_date_time_div">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Task Due Date Time</label>
                                <div class="col-sm-8 ps-0">
                                    <input type="text" class="form-control" id="lead_auto_task_due_date_time" name="lead_auto_task_due_date_time" readonly>
                                </div>
                            </div>

                            <div class="form-group row align-items-center mb-1" id="lead_auto_task_reminder_div">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Task Reminder</label>
                                <div class="col-sm-8 ps-0">
                                    <input type="text" class="form-control" id="lead_auto_task_reminder" name="lead_auto_task_reminder" readonly>
                                </div>
                            </div>

                            <div class="form-group row align-items-center mb-1" id="lead_auto_task_description_div">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Task Description</label>
                                <div class="col-sm-8 ps-0">
                                    <input type="text" class="form-control" id="lead_auto_task_description" name="lead_auto_task_description" readonly>
                                </div>
                            </div>
                            
                            <div for="" style="background-color: #eff2f7;font-weight: bold;" class="col-12 text-center mb-1">Call</div>

                            {{-- <div class="form-group row  align-items-center mb-1" id="lead_auto_call_type_div">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Type</label>
                                <div class="col-sm-8 ps-0" id="pointer_event_call_type">
                                    <select class="form-control select2-ajax" id="lead_auto_call_type_id" name="lead_auto_call_type_id"
                                        required=""></select>
                                    <div class="invalid-feedback">Please select type</div>
                                </div>
                            </div> --}}
        
                            
                           
                            <div class="form-group row  align-items-center mb-1 d-none" id="lead_auto_call_call_schedule_div">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Call Register</label>
                                <div class="col-sm-4 ps-0">
                                    <div class="input-group align-items-center" id="lead_auto_call_schedule_effective_date">
                                        <input type="text" class="form-control" value="{{ date('d-m-Y') }}"
                                            placeholder="dd-mm-yyyy" data-date-format="dd-mm-yyyy"
                                            data-date-container='#lead_auto_call_schedule_effective_date' data-provide="datepicker"
                                            data-date-autoclose="true" required name="lead_auto_call_schedule_date"
                                            id="lead_auto_call_schedule_date">
                                        <div class="position-absolute end-0 z-index-1 me-2" for="lead_task_due_date">
                                            <i class='bx bx-calendar bx-flashing'></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4 ps-0">
                                    <select class="form-control" id="lead_auto_call_schedule_time" name="lead_auto_call_schedule_time"
                                        required style="border: 1px solid #ced4da;border-radius: 0.25rem;">
                                        @foreach (getTimeSlot() as $timeSlot)
                                            <option value="{{ $timeSlot }}">{{ $timeSlot }} </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
        
                            <div class="form-group row  align-items-center mb-1" id="lead_auto_call_closing_note_div">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Closing Notes</label>
                                <div class="col-sm-8 ps-0">
                                    <textarea type="text" class="form-control" id="lead_auto_call_closing_note" name="lead_auto_call_closing_note" placeholder="Closing Notes" value="" rows="1"></textarea>
                                </div>
                            </div>
        
                            <div class="form-group row  align-items-center mb-1" id="lead_auto_call_outcome_div">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Call outcome</label>
                                <div class="col-sm-8 ps-0">
                                    <select class="form-control select2-ajax" id="lead_auto_call_outcome" name="lead_auto_call_outcome" required=""></select>
                                    <div class="invalid-feedback">Please select outcome type</div>
                                </div>
                            </div>

                            @if(Auth::user()->type == 9)
                                <div class="d-none" id="add_auto_info_div">
                                    <div class="form-group row  align-items-center mb-1">
                                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Architect</label>
                                        <div class="col-sm-8 ps-0">
                                            <input type="text" class="form-control" id="lead_auto_call_add_info_arc" name="lead_auto_call_add_info_arc" placeholder="Architect Name" value="">
                                        </div>
                                    </div>

                                    <div class="form-group row  align-items-center mb-1">
                                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Electrician</label>
                                        <div class="col-sm-8 ps-0">
                                            <input type="text" class="form-control" id="lead_auto_call_add_info_ele" name="lead_auto_call_add_info_ele" placeholder="Electrician Name" value="">
                                        </div>
                                    </div>

                                    <div class="form-group row  align-items-center mb-1">
                                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Additional Info</label>
                                        <div class="col-sm-8 ps-0">
                                            <select class="form-control select2-ajax" id="lead_auto_call_add_info" name="lead_auto_call_add_info"></select>
                                        </div>
                                    </div>

                                    <div class="form-group row  align-items-center mb-1 d-none" id="lead_auto_call_add_info_text_div">
                                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Additional Text</label>
                                        <div class="col-sm-8 ps-0">
                                            <input type="text" class="form-control" id="lead_auto_call_add_info_text" name="lead_auto_call_add_info_text" placeholder="Additional Text" value="">
                                        </div>
                                    </div>
                                </div>
                            @endif


                            <div id="auto_call_received_but_reschedule_div" class="d-none form-group row  align-items-center mb-1">
                                <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Call Re-Schedule</label>
                                <div class="col-sm-4 ps-0">
                                    <div class="input-group align-items-center" id="lead_auto_call_re_schedule_effective_date">
                                        <input type="text" class="form-control" value="{{ date('d-m-Y') }}"
                                            placeholder="dd-mm-yyyy" data-date-format="dd-mm-yyyy"
                                            data-date-container='#lead_auto_call_re_schedule_effective_date' data-provide="datepicker"
                                            data-date-autoclose="true" required name="lead_auto_re_call_schedule_date"
                                            id="lead_auto_re_call_schedule_date">
                                        <div class="position-absolute end-0 z-index-1 me-2" for="lead_auto_re_call_schedule_date">
                                            <i class='bx bx-calendar bx-flashing'></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4 ps-0">
                                    <select class="form-control" id="lead_auto_call_re_schedule_time" name="lead_auto_call_re_schedule_time" required style="border: 1px solid #ced4da;border-radius: 0.25rem;">
                                        @foreach (getTimeSlot() as $timeSlot)
                                            <option value="{{ $timeSlot }}">{{ $timeSlot }} </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <div id="autoCallFooter1" class="callFooter">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" id="auto_call_close_btn">Close</button>
                                    <button type="button" class="btn btn-primary save-btn">Save</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-6">
                    <table class="table table-striped table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>View</th>
                            </tr>
                        </thead>
                        <tbody id="leadCallAndTaskListBody">
                                                    
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTaskView" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog" aria-labelledby="modalInquiryLogLabel" aria-hidden="true" style="z-index: 1600;">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLeadLogLabel">Task Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="min-height:100%;">
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Task Title</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="task_title" name="" value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Task Status</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="task_status" name="" value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Deal Detail</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="task_lead_detail" name="" value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Created By</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="task_created_by" name="" value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Created At</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="task_created_at" name="" value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Due Date & Time</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="task_due_date_time" name="" value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Description</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="task_description" name="" value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Close Date & Time</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="task_close_date_time" name="" value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Close Note</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="task_close_note" name="" value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Outcome Type</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="task_outcome_type" name="" value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Task Architect</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="task_architect" name="" value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Task Electrician</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="task_electrician" name="" value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Task Additional Info</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="task_additional_info" name="" value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Task Additional Info Text</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="task_additional_info_text" name="" value="-" readonly="readonly">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCallView" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog" aria-labelledby="modalInquiryLogLabel" aria-hidden="true" style="z-index: 1600;">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLeadLogLabel">Call Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="min-height:100%;">
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Deal Detail</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_lead_detail" name="" value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Call Status</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_status" name="" value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Call Contact</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_contact" name="" value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Call Purpose</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_purpose" name="" value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Call Schedule</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_schedule" name="" value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Description</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_description" name="" value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Close Note</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_close_note" name="" value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Close Date & Time</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_close_date_time" name="" value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Outcome Type</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_outcome_type" name="" value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Call Reference</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_reference" name="" value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Call Architect</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_architect" name="" value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Call Electrician</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_electrician" name="" value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Call Additional Info</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_additional_info" name="" value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Call Additional Info Text</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_additional_info_tetx" name="" value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Created By</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_created_by" name="" value="-" readonly="readonly">
                    </div>
                </div>
                <div class="form-group row align-items-center mb-1">
                    <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Created At</label>
                    <div class="col-sm-8 ps-0">
                        <input type="text" class="form-control" id="call_created_at" name="" value="-" readonly="readonly">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalQuotationBrandView" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog" aria-labelledby="modalInquiryLogLabel" aria-hidden="true" style="z-index: 1600;">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLeadLogLabel">Brand</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="min-height:100%;">
                <table class="table table-striped dt-responsive nowrap w-100 dataTable no-footer dtr-inline">
                    <thead>
                        <tr>
                            <th>Brand</th>
                            <th>Discount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="BrandListPreview">
                        
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<style>
    .summary_table td,
        .summary_table th {
            vertical-align: middle !important;
        }

        .summary_table thead {
            background-color: #eff2f7;
        }

        .summary_table tbody,
        .summary_table td,
        .summary_table tfoot .summary_table th,
        .summary_table thead,
        .summary_table tr {
            border-color: #eff2f7;
            border-width: 1px !important;
        }
</style>
<input type="hidden" name="hidden_action_id" id="hidden_action_id">