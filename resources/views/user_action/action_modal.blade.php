<div class="modal fade" id="modalTask" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="modalTaskLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-s" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTaskLabel">Schedule Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form enctype="multipart/form-data" id="formUserTask" action="{{ route('user.action.task.save') }}"
                method="POST" class="needs-validation" novalidate>
                <div class="modal-body">
                    @csrf
                    <div class="col-md-12 text-center loadingcls">
                        <button type="button" class="btn btn-light waves-effect">
                            <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                        </button>
                    </div>
                    <input type="hidden" name="task_user_id" id="task_user_id">
                    <input type="hidden" name="task_id" id="task_id">

                    <div class="form-group row align-items-center border-bottom" id="task_assign_to_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Task Owner</label>
                        <div class="col-sm-8 ps-0" id="pointer_event_assign_to">
                            <select class="form-control select2-ajax" id="task_assign_to" name="task_assign_to"
                                required></select>
                            <div class="invalid-feedback">Please select assign</div>
                        </div>
                    </div>

                    <div class="form-group row align-items-center border-bottom" id="user_task_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Task</label>
                        <div class="col-sm-8 ps-0">
                            <input type="text" class="form-control border-none" id="user_task" name="user_task"
                                placeholder="Task" value="" required>
                        </div>
                    </div>

                    <div class="form-group row align-items-center border-bottom" id="task_due_date_time_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Due Date </label>
                        <div class="col-sm-4 ps-0">
                            <div class="input-group align-items-center" id="div_itemprice_effective_date">
                                <input type="text" class="form-control" value="{{ date('d-m-Y') }}" placeholder="dd-mm-yyyy" data-date-format="dd-mm-yyyy" data-date-container='#div_itemprice_effective_date' data-provide="datepicker" data-date-autoclose="true" required name="task_due_date" id="task_due_date">
                                <div class="position-absolute end-0 z-index-1 me-2" for="task_due_date">
                                    <i class='bx bx-calendar bx-flashing'></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 ps-0">
                            <select class="form-control" id="task_due_time" name="task_due_time" required style="border: 1px solid #ced4da;border-radius: 0.25rem;">
                                @foreach (getTimeSlot() as $timeSlot)
                                    <option value="{{ $timeSlot }}">{{ $timeSlot }} </option>
                                @endforeach
                            </select>
                        </div>
                        {{-- <div class="col-sm-8 ps-0">
                            <div class="input-group">
                                <input type="datetime-local" class="form-control border-none ps-2"
                                    id="task_due_date_time" placeholder="YYYY-MM-DD HH:ss" name="task_due_date_time"
                                    required autoComplete="off" />
                            </div>
                        </div> --}}
                    </div>

                    <div class="form-group row align-items-center border-bottom" id="task_reminder_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Reminder
                            Notification</label>
                        <div class="col-sm-8 ps-0">
                            <select class="form-control select2-ajax" id="task_reminder_date_time"
                                name="task_reminder_date_time" required=""></select>
                            <div class="invalid-feedback">Please select Reminder Time</div>
                        </div>
                        {{-- <div class="col-sm-8 ps-0">
                            <div class='input-group task_reminder'>
                                <input type="datetime-local" class="form-control border-none ps-2" id="task_reminder_id"
                                    placeholder="YYYY-MM-DD HH:ss" name="task_reminder" required autoComplete="off" />
                            </div>
                        </div> --}}
                    </div>

                    <div class="form-group row align-items-center border-bottom" id="task_description_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Task Note</label>
                        <div class="col-sm-8 ps-0">
                            <textarea type="text" class="form-control border-none" id="task_description" name="task_description"
                                placeholder="Task Note" value="" required rows="1"></textarea>
                        </div>
                    </div>

                    <div class="form-group row align-items-center border-bottom d-none" id="closing_note_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Closing Note</label>
                        <div class="col-sm-8 ps-0">
                            <textarea type="text" class="form-control border-none" id="task_closing_note" name="task_closing_note"
                                placeholder="Closing Note" value="" required rows="1"></textarea>
                        </div>
                    </div>

                    <div class="form-group row align-items-center border-bottom d-none" id="task_outcome_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Task outcome</label>
                        <div class="col-sm-8 ps-0">
                            <select class="form-control select2-ajax" id="task_outcome" name="task_outcome"
                                required=""></select>
                            <div class="invalid-feedback">Please select outcome type</div>
                        </div>
                    </div>
                    <input type="hidden" name="task_move_to_close" id="task_move_to_close" />
                </div>

                <div class="modal-footer">
                    <div id="taskfooter1">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button id="task_move_to_close_btn" type="button" class="btn btn-warning">Close
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
    <div class="modal-dialog modal-dialog-centered modal-s" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMeetingLabel">Set up Meeting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form enctype="multipart/form-data" id="formUserMeeting" action="{{ route('user.action.meeting.save') }}"
                method="POST" class="needs-validation" novalidate>
                <div class="modal-body">
                    @csrf
                    <div class="col-md-12 text-center loadingcls">
                        <button type="button" class="btn btn-light waves-effect">
                            <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i>Loading
                        </button>
                    </div>
                    <input type="hidden" name="meeting_user_id" id="meeting_user_id">
                    <input type="hidden" name="meeting_id" id="meeting_id">

                    <div class="form-group row align-items-center border-bottom" id="meeting_title_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Title</label>
                        <div class="col-sm-8 ps-0" id="pointer_event_meeting_title">
                            <select class="form-control select2-ajax" id="meeting_title_id" name="meeting_title_id"
                                required=""></select>
                            <div class="invalid-feedback">Please select title </div>
                        </div>
                    </div>

                    <div class="form-group row align-items-center border-bottom" id="meeting_type_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Type</label>
                        <div class="col-sm-8 ps-0" id="pointer_event_meeting_type">
                            <select class="form-control select2-ajax" id="meeting_type_id" name="meeting_type_id"
                                required=""></select>
                            <div class="invalid-feedback">Please select type</div>
                        </div>
                    </div>

                    <div class="form-group row align-items-center border-bottom" id="meeting_location_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Location</label>
                        <div class="col-sm-8 ps-0">
                            <input type="text" class="form-control border-none" id="meeting_location"
                                name="meeting_location" placeholder="Location Name" value="" required>
                        </div>
                    </div>

                    <div class="form-group row align-items-center border-bottom" id="meeting_date_time_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Date & Time</label>
                        <div class="col-sm-4 ps-0">
                            <div class="input-group align-items-center" id="meeting_effective_date">
                                <input type="text" class="form-control" value="{{ date('d-m-Y') }}"
                                    placeholder="dd-mm-yyyy" data-date-format="dd-mm-yyyy"
                                    data-date-container='#meeting_effective_date' data-provide="datepicker"
                                    data-date-autoclose="true" required name="meeting_date"
                                    id="meeting_date">
                                <div class="position-absolute end-0 z-index-1 me-2">
                                    <i class='bx bx-calendar bx-flashing'></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 ps-0">
                            <select class="form-control" id="meeting_time" name="meeting_time" required
                                style="border: 1px solid #ced4da;border-radius: 0.25rem;">
                                @foreach (getTimeSlot() as $timeSlot)
                                    <option value="{{ $timeSlot }}">{{ $timeSlot }} </option>
                                @endforeach
                            </select>
                        </div>
                        {{-- <div class="col-sm-8 ps-0">
                            <div class='input-group'>
                                <input type="datetime-local" class="form-control border-none" id="meeting_date_time"
                                    placeholder="YYYY-MM-DD HH:ss" name="meeting_date_time" required
                                    autoComplete="off" />
                            </div>
                        </div> --}}
                    </div>

                    <div class="form-group row align-items-center border-bottom" id="meeting_is_notification_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Reminder
                            Notification</label>
                            <div class="col-sm-8 ps-0">
                                <select class="form-control select2-ajax" id="meeting_reminder_date_time"
                                    name="meeting_reminder_date_time" required=""></select>
                                <div class="invalid-feedback">Please select reminder Time</div>
                            </div>
                        {{-- <div class="col-sm-8 ps-0">
                            <div class='input-group meeting_reminder'>
                                <input type="datetime-local" class="form-control border-none ps-2"
                                    id="meeting_reminder_id" placeholder="YYYY-MM-DD HH:ss" name="meeting_reminder"
                                    required autoComplete="off" />
                            </div>
                        </div> --}}
                    </div>

                    <div class="form-group row align-items-center border-bottom" id="meeting_participants_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Participants</label>
                        <div class="col-sm-8 ps-0" id="pointer_event_meeting_participants">
                            <select multiple="multiple" class="form-control select2-ajax select2-multiple"
                                id="meeting_participants" name="meeting_participants[]"></select>
                            <div class="invalid-feedback">Please select title </div>
                        </div>
                    </div>

                    <div class="form-group row align-items-center border-bottom" id="meeting_note_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label"
                            id="meeting_description_label">Meeting Notes</label>
                        <div class="col-sm-8 ps-0">
                            <textarea type="text" class="form-control border-none" id="meeting_description" name="meeting_description"
                                placeholder="Meeting Notes" value="" required rows="1"></textarea>
                        </div>
                    </div>

                    <div class="form-group row align-items-center border-bottom d-none" id="meeting_closing_note_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Closing Notes</label>
                        <div class="col-sm-8 ps-0">
                            <textarea type="text" class="form-control border-none" id="close_meeting_note" name="close_meeting_note"
                                placeholder="Description" value="" required rows="1"></textarea>
                        </div>
                    </div>

                    <div class="form-group row align-items-center border-bottom d-none" id="meeting_outcome_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Meeting
                            outcome</label>
                        <div class="col-sm-8 ps-0">
                            <select class="form-control select2-ajax" id="meeting_outcome" name="meeting_outcome"
                                required=""></select>
                            <div class="invalid-feedback">Please select outcome type</div>
                        </div>
                    </div>

                    <input type="hidden" id="meeting_move_to_close" name="meeting_move_to_close" />
                </div>
                <div class="modal-footer">
                    <div id="meetingFooter1">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button id="meeting_move_to_close_btn" type="button" class="btn btn-warning">Close
                            Meeting</button>
                        <button type="submit" class="btn btn-primary save-btn">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="modalCall" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="modalCallLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-s" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCallLabel">Call</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form enctype="multipart/form-data" id="formUserCall" action="{{ route('user.action.call.save') }}"
                method="POST" class="needs-validation" novalidate>
                <div class="modal-body p-0 m-4">
                    @csrf
                    <div class="col-md-12 text-center loadingcls">
                        <button type="button" class="btn btn-light waves-effect">
                            <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                        </button>
                    </div>
                    <input type="hidden" name="call_user_id" id="call_user_id">
                    <input type="hidden" name="call_id" id="call_id">
                    <div class="form-group row  align-items-center border-bottom" id="call_type_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Type</label>
                        <div class="col-sm-8 ps-0" id="pointer_event_call_type">
                            <select class="form-control select2-ajax" id="call_type_id" name="call_type_id"
                                required=""></select>
                            <div class="invalid-feedback">Please select type</div>
                        </div>
                    </div>

                    <div class="form-group row  align-items-center border-bottom" id="call_contact_name_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Contact Name</label>
                        <div class="col-sm-8 ps-0" id="pointer_event_call_contact_name">
                            <select class="form-control select2-ajax" id="call_contact_name" name="call_contact_name"
                                required=""></select>
                            <div class="invalid-feedback">Please select Contact</div>
                        </div>
                    </div>

                    <div class="form-group row  align-items-center border-bottom" id="call_call_schedule_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Call Schedule</label>
                        <div class="col-sm-4 ps-0">
                            <div class="input-group align-items-center" id="call_schedule_effective_date">
                                <input type="text" class="form-control" value="{{ date('d-m-Y') }}"
                                    placeholder="dd-mm-yyyy" data-date-format="dd-mm-yyyy"
                                    data-date-container='#call_schedule_effective_date' data-provide="datepicker"
                                    data-date-autoclose="true" required name="call_schedule_date"
                                    id="call_schedule_date">
                                <div class="position-absolute end-0 z-index-1 me-2">
                                    <i class='bx bx-calendar bx-flashing'></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 ps-0">
                            <select class="form-control" id="call_schedule_time" name="call_schedule_time" required
                                style="border: 1px solid #ced4da;border-radius: 0.25rem;">
                                @foreach (getTimeSlot() as $timeSlot)
                                    <option value="{{ $timeSlot }}">{{ $timeSlot }} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row  align-items-center border-bottom" id="call_reminder_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Reminder
                            Notification</label>
                        <div class="col-sm-8 ps-0">
                            <select class="form-control select2-ajax" id="call_reminder_date_time"
                                name="call_reminder_date_time" required=""></select>
                            <div class="invalid-feedback">Please select reminder Time</div>
                        </div>
                    </div>

                    <div class="form-group row  align-items-center border-bottom" id="call_purpose_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Call Purpose</label>
                        <div class="col-sm-8 ps-0">
                            <input type="text" class="form-control border-none" id="call_purpose"
                                name="call_purpose" placeholder="Call Purpose" value="" required>
                        </div>
                    </div>

                    <div class="form-group row  align-items-center border-bottom" id="call_notes_div">
                        <label for="horizontal-firstname-input" id="call_notes_label"
                            class="col-sm-4 col-form-label">Call Notes</label>
                        <div class="col-sm-8 ps-0">
                            <textarea type="text" class="form-control border-none" id="call_description" name="call_description"
                                placeholder="Call Notes" value="" rows="1"></textarea>
                        </div>
                    </div>

                    <div class="form-group row  align-items-center border-bottom d-none" id="call_closing_note_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Closing Notes</label>
                        <div class="col-sm-8 ps-0">
                            <textarea type="text" class="form-control border-none" id="call_closing_note" name="call_closing_note"
                                placeholder="Closing Notes" value="" rows="1"></textarea>
                        </div>
                    </div>

                    <div class="form-group row  align-items-center border-bottom d-none" id="call_call_outcome_div">
                        <label for="horizontal-firstname-input" class="col-sm-4 col-form-label">Call outcome</label>
                        <div class="col-sm-8 ps-0">
                            <select class="form-control select2-ajax" id="call_call_outcome" name="call_call_outcome"
                                required=""></select>
                            <div class="invalid-feedback">Please select outcome type</div>
                        </div>
                    </div>

                    <input type="hidden" name="call_move_to_close" id="call_move_to_close" />
                    <div class="modal-footer">
                        <div id="callFooter1" class="callFooter">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="button" id="call_move_to_close_btn" class="btn btn-warning">Close
                                Call</button>
                            <button type="submit" class="btn btn-primary save-btn">Save</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalFile" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="modalFileLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFileLabel"> Lead File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form enctype="multipart/form-data" id="formUserFile" action="{{ route('user.action.file.save') }}" method="POST" class="needs-validation" novalidate>
                <div class="modal-body">
                    @csrf
                    <div class="col-md-12 text-center loadingcls">
                        <button type="button" class="btn btn-light waves-effect">
                            <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                        </button>
                    </div>

                    <input type="hidden" name="file_user_id" id="file_user_id">

                    <div class="row">
                        <div class="col-md-6">

                            <div class="row mb-1">
                                <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Tag</label>
                                <div class="col-sm-9">
                                    <select class="form-control select2-ajax" id="file_tag_id"
                                        name="file_tag_id" required>

                                    </select>
                                    <div class="invalid-feedback">
                                        Please select tag
                                    </div>
                                </div>
                            </div>




                        </div>

                        <div class="col-md-6">

                            <div class="row mb-1">
                                <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">File</label>
                                <div class="col-sm-9">
                                    <input type="file" class="form-control" id="file_name"
                                        name="file_name" placeholder="File" value="" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">


                    <div>


                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button id="btnSaveFile" type="submit" class="btn btn-primary save-btn">Save</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalUserContact" data-bs-backdrop="static" tabindex="-1" role="dialog"
    aria-labelledby="modalUserContactLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUserContactLabel"> Lead Contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form enctype="multipart/form-data" id="formUserContact" action="{{ route('user.action.contact.save') }}" method="POST" class="needs-validation" novalidate>
                <div class="modal-body">
                    @csrf
                    <div class="col-md-12 text-center loadingcls">
                        <button type="button" class="btn btn-light waves-effect">
                            <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                        </button>
                    </div>

                    <input type="hidden" name="contact_user_id" id="contact_user_id">
                    <input type="hidden" name="contact_id" id="contact_id">

                        <div class="row mb-1 border-bottom">
                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">First name <code class="highlighter-rouge">*</code></label>
                            <div class="col-sm-9">
                                <input class="form-control" id="contact_first_name" name="contact_first_name"
                                    placeholder="First Name" value="" required>
                            </div>
                        </div>
                        
                        <div class="row mb-1 border-bottom">
                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Last name <code class="highlighter-rouge">*</code></label>
                            <div class="col-sm-9">
                                <input class="form-control" id="contact_last_name" name="contact_last_name"
                                    placeholder="Last Name" value="" required>
                            </div>
                        </div>
                        <div class="row mb-1 border-bottom">
                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label py-0">Phone number <code class="highlighter-rouge">*</code></label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <div class="input-group-text">
                                        +91
                                    </div>
                                    <input type="number" class="form-control" id="contact_phone_number"
                                        name="contact_phone_number" placeholder="Phone number" value=""
                                        required>

                                </div>
                            </div>
                        </div>

                        
                        
                        <div class="row mb-1 border-bottom">
                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label py-0">Alternate
                                Phone number</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <div class="input-group-text">
                                        +91
                                    </div>
                                    <input type="number" class="form-control" id="contact_alernate_phone_number"
                                    name="contact_alernate_phone_number" placeholder="Phone number"
                                        value="">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-1 border-bottom">
                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Email</label>
                            <div class="col-sm-9">
                                <input class="form-control" id="contact_email" name="contact_email"
                                    placeholder="Email" value="">
                            </div>
                        </div>

                        <div class="row mb-1">
                            <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Tag <code class="highlighter-rouge">*</code></label>
                            <div class="col-sm-9">
                                <select class="form-control select2-ajax" id="contact_tag_id" name="contact_tag_id"
                                    required>

                                </select>
                                <div class="invalid-feedback">
                                    Please select tag
                                </div>
                            </div>
                        </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button id="btnSaveContact" type="submit" class="btn btn-primary save-btn">Save</button>
                </div>

            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRewardPoint" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1" role="dialog" aria-labelledby="modalInquiryLogLabel" aria-hidden="true" style="z-index: 1400;">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLeadLogLabel">Reward Point</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="min-height:100%;">
                <table id="RewardPoint" class="table align-middle table-nowrap mb-0 w-100">
                    <thead>
                        <tr>
                            <th>Bill Attached</th>
                            <th>Bill Amount</th>
                            <th>Point</th>
                            <th>Query</th>
                            <th>Lapsed</th>
                            <th>Claim</th>
                            <th>HOD Approved</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
            {{-- <div class="modal-footer">
                <button class="btn btn-primary" onclick="SaveBillingAmount()">Save</button>
            </div> --}}
        </div>
    </div>
</div>

<input type="hidden" name="hidden_action_id" id="hidden_action_id">
