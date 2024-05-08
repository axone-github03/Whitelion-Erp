<script>
    var ajaxURLSearchCallType = "{{ route('crm.lead.search.call.type') }}";
    var ajaxURLSearchCallOutcomeType = "{{ route('crm.lead.search.call.outcome.type') }}";
    var ajaxURLSearchCallAssignTo = "{{ route('crm.lead.search.call.assign') }}";
    var ajaxURLSearchMeetingType = "{{ route('crm.lead.search.meeting.type') }}";
    var ajaxURLSearchContact = "{{ route('crm.lead.search.contact') }}";
    var ajaxURLSearchMeetingTitle = "{{ route('crm.lead.search.meeting.title') }}";
    var ajaxURLSearchMeetingOutcomeType = "{{ route('crm.lead.search.meeting.outcome.type') }}";
    var ajaxURLSearchTaskAssignTo = "{{ route('crm.lead.search.task.assign') }}";
    var ajaxURLSearchMeetingParticipants = "{{ route('crm.lead.search.meeting.participants') }}";
    var ajaxURLSearchTaskOutcomeType = "{{ route('crm.lead.search.task.outcome.type') }}";
    var ajaxURLSearchStatus = "{{ route('crm.lead.search.status.action') }}";
    var ajaxURLSearchReminderTimeSlot = "{{ route('crm.lead.search.reminder.time.slot') }}";
    var ajaxURLFindMeetingTimes = "{{ route('crm.lead.find.meeting.times') }}";
    var ajaxURLAdditionalInfo = "{{ route('crm.lead.search.additional.info') }}";
    var ajaxURLAddInfoDetail = "{{ route('crm.lead.additional.info.detail') }}";
    var ajaxURLOutComeTypeDetail = "{{ route('crm.lead.call.outcome.type.detail') }}";

    var ajaxURLCallDetail = "{{ route('crm.lead.call.detail') }}";
    var ajaxURLTaskDetail = "{{ route('crm.lead.task.detail') }}";
    var ajaxURLMeetingDetail = "{{ route('crm.lead.meeting.detail') }}";

    var ajaxURLAutoTaskDetail = "{{ route('crm.lead.auto.task.detail') }}";
    var ajaxURLAutoCallDetail = "{{ route('crm.lead.auto.call.detail') }}";
    var ajaxURLAutoTaskAndCallList = "{{ route('crm.lead.auto.task.and.call.list') }}";
    var ajaxURLQuotationBrandList = "{{ route('quot.get.brand.list') }}";
    var ajaxURLQuotDiscountApprovedOrReject = "{{ route('quot.discount.approved.or.reject') }}";


    var is_meeting_schedule = 0;
    var ScheduleTimeTable = null;
    var csrfToken = $("[name=_token").val();

    $(function() {
        $('.datetimepicker').datetimepicker({
            format: 'dd:mm:yyyy HH:ss a'
        });
    });

    function viewCall(id, type) {
        $('#hidden_action_id').val(id + '-call');
        $("#modalCall").modal('show');
        $("#lead_call_type_id").empty().trigger('change');
        $("#lead_call_contact_name").empty().trigger('change');
        $('#formLeadCall').trigger("reset");
        $('#call_close_btn').removeClass('d-none');
        $('#call_close_cross_btn').removeClass('d-none');
        $('#lead_call_autogenerate').val(0);
        $('#lead_call_ref_id').val(0);
        $('#add_info_div').addClass('d-none');
        $('#call_received_but_reschedule_div').addClass('d-none');

        if (type == 'close') {
            $('#modalCallLabel').text('Close Call');
            $('#callFooter1 .save-btn').addClass('d-none');
        } else if (type == 'open') {
            $('#callFooter1 .save-btn').removeClass('d-none');
        }

        var status = $(".status-active-class").text();
        $.ajax({
            type: 'GET',
            url: ajaxURLCallDetail + "?id=" + id,
            success: function(responseText) {
                if (responseText['status'] == 1) {
                    $("#formLeadCall .loadingcls").hide();
                    $("#lead_call_lead_id").val(responseText['data']['lead_id']);
                    $("#lead_call_id").val(responseText['data']['id']);

                    if (responseText['data']['reference_id'] != 0 && responseText['data'][
                        'reference_type'] == 'Task') {
                        $('#lead_call_autogenerate').val(1);
                        $('#lead_call_ref_id').val(responseText['data']['reference_id']);
                        $('#add_info_div').removeClass('d-none');
                    }

                    $("#lead_call_schedule_date").val(responseText['data']['schedule_date']);

                    var newOption = new Option(responseText['data']['schedule_time'], responseText['data'][
                        'schedule_time'
                    ], false, false);
                    $('#lead_call_schedule_time').append(newOption).trigger('change');
                    $("#lead_call_schedule_time").val("" + responseText['data']['schedule_time'] + "");
                    $('#lead_call_schedule_time').trigger('change');

                    var newOption = new Option(responseText['data']['reminder_text'], responseText['data'][
                        'reminder_id'
                    ], false, false);
                    $('#lead_call_reminder_date_time').append(newOption).trigger('change');
                    $("#lead_call_reminder_date_time").val("" + responseText['data']['reminder_id'] + "");
                    $('#lead_call_reminder_date_time').trigger('change');

                    $("#lead_call_call_description").val(responseText['data']['description']);

                    $("#lead_call_call_purpose").val(responseText['data']['purpose']);

                    var newOption = new Option(responseText['data']['type']['text'], responseText['data'][
                        'type'
                    ]['id'], false, false);
                    $('#lead_call_type_id').append(newOption).trigger('change');
                    $("#lead_call_type_id").val("" + responseText['data']['type']['id'] + "");
                    $('#lead_call_type_id').trigger('change');



                    var newOption = new Option(responseText['data']['contact_name']['text'], responseText[
                        'data']['contact_name']['id'], false, false);
                    $('#lead_call_contact_name').append(newOption).trigger('change');
                    $("#lead_call_contact_name").val("" + responseText['data']['contact_name']['id'] + "");
                    $('#lead_call_contact_name').trigger('change');

                    if ($('#hidden_user_type').val() == 9) {
                        if (responseText['data']['assign_to']['id'] == $('#hidden_user_id').val()) {
                            var newOption = new Option("SELF", "0", false, false);
                            $('#lead_call_assign_user').append(newOption).trigger('change');
                            $("#lead_call_assign_user").val("" + "0" + "");
                            $('#lead_call_assign_user').trigger('change');
                        } else {
                            var newOption = new Option(responseText['data']['assign_to']['text'],
                                responseText['data']['assign_to']['id'], false, false);
                            $('#lead_call_assign_user').append(newOption).trigger('change');
                            $("#lead_call_assign_user").val("" + responseText['data']['assign_to']['id'] +
                                "");
                            $('#lead_call_assign_user').trigger('change');
                        }
                    }

                    if (responseText['data']['lead_type']['is_deal'] == 0) {
                        $('#lead_and_deal_call_status').val("Lead - " + status + "");
                    } else if (responseText['data']['lead_type']['is_deal'] == 1) {
                        $('#lead_and_deal_call_status').val("Deal - " + status + "");
                    }

                    var newOption = new Option(responseText['data']['lead_status'], responseText['data'][
                        'lead_type'
                    ]['status'], false, false);
                    $('#lead_call_status').append(newOption).trigger('change');
                    $("#lead_call_status").val("" + responseText['data']['lead_type']['status'] + "");
                    $('#lead_call_status').trigger('change');


                    $("#lead_call_move_to_close").val(0);
                    if (responseText['data']['is_closed'] == 0) {
                        $("#lead_call_move_to_close_btn").show();
                        $('#lead_call_purpose_div').addClass('d-none');
                        $('#lead_call_status_div').removeClass('d-none');
                        $('#lead_call_outcome_div').removeClass('d-none');
                        $('#lead_call_closing_note_div').removeClass('d-none');
                        $('#modalCallLabel').text('Close Call');
                        $("#callFooter1 .save-btn").hide();
                        $('#callFooter1 .save-btn').addClass('d-none');

                        $('#lead_call_assign_user_div, #select2-lead_call_assign_user-container, #lead_call_type_div, #lead_call_contact_name_div, #lead_call_call_schedule_div, #lead_call_reminder_div, #lead_call_purpose_div, #lead_call_notes_div, #select2-lead_call_type_id-container, #lead_call_schedule_date, #lead_call_reminder_date, #select2-lead_call_reminder_time-container, #select2-lead_call_schedule_time-container, #select2-lead_call_contact_name-container, #lead_call_call_description, #lead_call_purpose, #lead_call_reminder, #select2-lead_call_reminder_date_time-container')
                            .addClass('bg-light')
                        $('#lead_call_call_description').attr('readonly', true);
                        $('#pointer_event_call_assign_user, #pointer_event_call_type, #pointer_event_call_contact_name, #lead_call_call_schedule_div, #lead_call_reminder_div')
                            .addClass('pe-none');
                    } else {
                        var newOption = new Option(responseText['data']['outcome_type']['text'],
                            responseText['data']['outcome_type']['id'], false, false);
                        $('#lead_call_call_outcome').append(newOption).trigger('change');
                        $("#lead_call_call_outcome").val("" + responseText['data']['outcome_type']['id'] +
                            "");
                        $('#lead_call_call_outcome').trigger('change');
                        $("#lead_call_move_to_close_btn").hide();
                        // $('#callFooter1 .save-btn').removeClass('d-none');
                        $("#callFooter1 .save-btn").show();
                        $('#modalCallLabel').text('Call');
                    }


                    // $(".callFooter").hide();
                    //$("#callFooter2").show();

                } else {

                    if (typeof responseText['data'] !== "undefined") {

                        var size = Object.keys(responseText['data']).length;
                        if (size > 0) {

                            for (var [key, value] of Object.entries(responseText['data'])) {

                                toastr["error"](value);
                            }

                        }

                    } else {
                        toastr["error"](responseText['msg']);
                    }

                }
            }
        });
    }

    function viewMeeting(id, type) {

        is_meeting_schedule = 1;

        $('#hidden_action_id').val(id + '-meeting');
        if (type == 'close') {
            $('#modalMeetingLabel').text('Close Meeting');
            $('#meetingFooter1 .save-btn').addClass('d-none');
        } else if (type == 'open') {
            $('#meetingFooter1 .save-btn').removeClass('d-none');
        }
        $("#modalMeeting").modal('show');

        $('#meeting_date_schedule').addClass('d-none');
        $('#suggested_time').addClass('d-none');
        $('#meeting_form').addClass('col-12').removeClass('col-7');


        $("#lead_meeting_title_id").empty().trigger('change');
        $("#lead_meeting_participants").empty().trigger('change');
        $('#formLeadMeeting').trigger("reset");
        var status = $(".status-active-class").text();
        $.ajax({
            type: 'GET',
            url: ajaxURLMeetingDetail + "?id=" + id,
            success: function(responseText) {
                if (responseText['status'] == 1) {
                    $("#formLeadMeeting .loadingcls").hide();
                    $("#lead_meeting_lead_id").val(responseText['data']['lead_id']);
                    $("#lead_meeting_id").val(responseText['data']['id']);
                    $("#lead_meeting_location").val(responseText['data']['location']);
                    $("#lead_meeting_description").val(responseText['data']['description']);
                    // SIMPLE

                    $("#lead_meeting_date").val(responseText['data']['meeting_date']);

                    var newOption = new Option(responseText['data']['meeting_time'], responseText['data'][
                        'meeting_time'
                    ], false, false);
                    $('#lead_meeting_time').append(newOption).trigger('change');
                    $("#lead_meeting_time").val("" + responseText['data']['meeting_time'] + "");
                    $('#lead_meeting_time').trigger('change');

                    var newOption = new Option(responseText['data']['meeting_interval_time']['text'],
                        responseText['data']['meeting_interval_time']['id'], false, false);
                    $('#lead_meeting_interval_time').append(newOption).trigger('change');
                    $("#lead_meeting_interval_time").val("" + responseText['data']['meeting_interval_time'][
                        'id'
                    ] + "");
                    $('#lead_meeting_interval_time').trigger('change');

                    var newOption = new Option(responseText['data']['reminder_text'], responseText['data'][
                        'reminder_id'
                    ], false, false);
                    $('#lead_meeting_reminder_date_time').append(newOption).trigger('change');
                    $("#lead_meeting_reminder_date_time").val("" + responseText['data']['reminder_id'] +
                        "");
                    $('#lead_meeting_reminder_date_time').trigger('change');

                    var newOption = new Option(responseText['data']['title']['text'], responseText['data'][
                        'title'
                    ]['id'], false, false);
                    $('#lead_meeting_title_id').append(newOption).trigger('change');
                    $("#lead_meeting_title_id").val("" + responseText['data']['title']['id'] + "");
                    $('#lead_meeting_title_id').trigger('change');

                    var newOption = new Option(responseText['data']['type']['text'], responseText['data'][
                        'type'
                    ]['id'], false, false);
                    $('#lead_meeting_type_id').append(newOption).trigger('change');
                    $("#lead_meeting_type_id").val("" + responseText['data']['type']['id'] + "");
                    $('#lead_meeting_type_id').trigger('change');


                    var newOption = new Option(responseText['data']['lead_status'], responseText['data'][
                        'lead_type'
                    ]['status'], false, false);
                    $('#lead_meeting_status').append(newOption).trigger('change');
                    $("#lead_meeting_status").val("" + responseText['data']['lead_type']['status'] + "");
                    $('#lead_meeting_status').trigger('change');


                    $("#lead_meeting_participants").empty().trigger('change');
                    var selectedParticipant = [];
                    for (var i = 0; i < responseText['data']['lead_meeting_participant'].length; i++) {
                        selectedParticipant.push('' + responseText['data']['lead_meeting_participant'][i][
                            'id'
                        ] + '');
                        var newOption = new Option(responseText['data']['lead_meeting_participant'][i][
                            'text'
                        ], responseText['data']['lead_meeting_participant'][i]['id'], false, false);
                        $('#lead_meeting_participants').append(newOption).trigger('change');
                    }
                    $("#lead_meeting_participants").val(selectedParticipant).change();
                    $("#lead_meeting_move_to_close").val(0);
                    if (responseText['data']['lead_type']['is_deal'] == 0) {
                        $('#lead_and_deal_meeting_status').val("Lead - " + status + "");
                    } else if (responseText['data']['lead_type']['is_deal'] == 1) {
                        $('#lead_and_deal_meeting_status').val("Deal - " + status + "");
                    }
                    if (responseText['data']['is_closed'] == 0) {
                        $("#lead_meeting_move_to_close_btn").show();
                        $('#modalMeetingLabel').text('Close Meeting');

                        $('#lead_meeting_closing_note_div').removeClass('d-none');
                        $('#lead_meeting_outcome_div').removeClass('d-none');
                        $('#lead_meeting_status_div').removeClass('d-none');

                        $("#meetingFooter1 .save-btn").hide();

                        $('#select2-lead_meeting_interval_time-container, #lead_meeting_title_div, #lead_meeting_type_div, #lead_meeting_location_div, #lead_meeting_meeting_date_time_div, #lead_meeting_interval_time_div, #lead_meeting_is_notification_div, #lead_meeting_participants_div, #lead_meeting_note_div, #select2-lead_meeting_title_id-container, #select2-lead_meeting_type_id-container, #lead_meeting_location, #lead_meeting_description, #lead_meeting_date, #lead_meeting_reminder_date, #select2-lead_meeting_time-container, #select2-lead_meeting_reminder_date_time-container')
                            .addClass('bg-light')
                        $('#lead_meeting_participants_div .select2-selection--multiple').addClass(
                            'bg-light');
                        $('#lead_meeting_location, #lead_meeting_description').attr('readonly', true);

                        $('#pointer_event_meeting_participants, #pointer_event_meeting_title, #pointer_event_meeting_type, #lead_meeting_meeting_date_time_div, #lead_meeting_interval_time_div, #lead_meeting_is_notification_div')
                            .addClass('pe-none');
                    } else {
                        $("#lead_meeting_move_to_close_btn").hide();
                        $("#meetingFooter1 .save-btn").show();
                    }
                    // $(".callFooter").hide();
                    //$("#callFooter2").show();
                } else {

                    if (typeof responseText['data'] !== "undefined") {

                        var size = Object.keys(responseText['data']).length;
                        if (size > 0) {

                            for (var [key, value] of Object.entries(responseText['data'])) {

                                toastr["error"](value);
                            }

                        }

                    } else {
                        toastr["error"](responseText['msg']);
                    }

                }
            }
        });



    }

    function viewTask(id, type) {
        $('#hidden_action_id').val(id + '-task');
        if (type == 'close') {
            $('#taskfooter1 .save-btn').addClass('d-none');
            $('#modalTaskLabel').text('Close Task');
        } else if (type == 'open') {
            $('#taskfooter1 .save-btn').removeClass('d-none');
        }
        $("#modalTask").modal('show');
        $("#lead_task_assign_to").empty().trigger('change');
        $('#formLeadTask').trigger("reset");
        var status = $(".status-active-class").text();
        $.ajax({
            type: 'GET',
            url: ajaxURLTaskDetail + "?id=" + id,
            success: function(responseText) {
                if (responseText['status'] == 1) {
                    $("#formLeadTask .loadingcls").hide();
                    $("#lead_task_lead_id").val(responseText['data']['lead_id']);
                    $("#lead_task_id").val(responseText['data']['id']);

                    $("#lead_task_task").val(responseText['data']['task']);

                    $("#lead_task_due_date").val(responseText['data']['due_date']);
                    if (responseText['data']['is_autogenerate'] == 1) {
                        $('#lead_task_auto_generate').val(1);
                    }

                    var newOption = new Option(responseText['data']['due_time'], responseText['data'][
                        'due_time'
                    ], false, false);
                    $('#lead_task_due_time').append(newOption).trigger('change');
                    $("#lead_task_due_time").val("" + responseText['data']['due_time'] + "");
                    $('#lead_task_due_time').trigger('change');

                    var newOption = new Option(responseText['data']['reminder_text'], responseText['data'][
                        'reminder_id'
                    ], false, false);
                    $('#lead_task_reminder_date_time').append(newOption).trigger('change');
                    $("#lead_task_reminder_date_time").val("" + responseText['data']['reminder_id'] + "");
                    $('#lead_task_reminder_date_time').trigger('change');

                    $('#lead_task_due_date_time').val(responseText['data']['due_date_time']);
                    $('#lead_task_reminder_id').val(responseText['data']['reminder']);

                    $("#lead_task_description").val(responseText['data']['description']);

                    var newOption = new Option(responseText['data']['assign_to']['text'], responseText[
                        'data']['assign_to']['id'], false, false);
                    $('#lead_task_assign_to').append(newOption).trigger('change');
                    $("#lead_task_assign_to").val("" + responseText['data']['assign_to']['id'] + "");
                    $('#lead_task_assign_to').trigger('change');

                    $("#lead_task_move_to_close").val(0);

                    if (responseText['data']['lead_type']['is_deal'] == 0) {
                        $('#lead_and_deal_task_status').val("Lead - " + status + "")
                    } else if (responseText['data']['lead_type']['is_deal'] == 1) {
                        $('#lead_and_deal_task_status').val("Deal - " + status + "")
                    }

                    var newOption = new Option(responseText['data']['lead_status'], responseText['data'][
                        'lead_type'
                    ]['status'], false, false);
                    $('#lead_task_status').append(newOption).trigger('change');
                    $("#lead_task_status").val("" + responseText['data']['lead_type']['status'] + "");
                    $('#lead_task_status').trigger('change');


                    if (responseText['data']['is_closed'] == 0) {
                        $("#lead_task_move_to_close_btn").show();
                        $('#modalTaskLabel').text('Close Task');
                        $('#lead_status_div').removeClass('d-none');
                        $('#lead_closing_note_div').removeClass('d-none');
                        $('#lead_task_outcome_div').removeClass('d-none');
                        $('#taskfooter1 .save-btn').hide();

                        $('#lead_task_assign_to_div, #lead_task_div, #lead_task_due_date_time_div, #lead_task_reminder_div, #lead_task_description_div, #select2-lead_task_assign_to-container, #lead_task_task, #lead_task_description, #lead_task_due_date, #select2-lead_task_due_time-container, #lead_task_reminder_date, #select2-lead_task_reminder_date_time-container')
                            .addClass('bg-light')

                        $('#lead_task_task, #lead_task_description')
                            .attr('readonly', true);
                        $('#pointer_event_assign_to, #lead_task_due_date_time_div, #lead_task_reminder_div')
                            .addClass('pe-none');
                    } else {
                        $("#lead_task_move_to_close_btn").hide();
                        $('#taskfooter1 .save-btn').show();
                    }


                    // $(".callFooter").hide();
                    //$("#callFooter2").show();

                } else {

                    if (typeof responseText['data'] !== "undefined") {

                        var size = Object.keys(responseText['data']).length;
                        if (size > 0) {

                            for (var [key, value] of Object.entries(responseText['data'])) {

                                toastr["error"](value);
                            }

                        }

                    } else {
                        toastr["error"](responseText['msg']);
                    }

                }
            }
        });



    }

    function viewAutoScheduleAction(id, type) {
        $("#modalAutoScheduleCall").modal('show');
        $("#formAutogenerateAction .loadingcls").hide();
        $("#lead_auto_call_type_id").empty().trigger('change');
        $("#lead_auto_call_outcome").empty().trigger('change');
        $('#auto_call_received_but_reschedule_div').addClass('d-none');
        $('#formLeadCall').trigger('reset');

        if(type == 'task'){
            $('#lead_auto_task_id').val(0);
            $.ajax({
                type: 'GET',
                url: ajaxURLTaskDetail + "?id=" + id,
                success: function(responseText) {
                    if (responseText['status'] == 1) {
                        $("#formLeadTask .loadingcls").hide();
                        TaskAndCallList(responseText['data']['lead_id'])
                        $("#lead_auto_call_lead_id").val(responseText['data']['lead_id']);
                        $("#lead_auto_task_id").val(responseText['data']['id']);

                        $('#lead_auto_task_assign_to').val(responseText['data']['assign_to']['text']);
                        $("#lead_auto_task").val(responseText['data']['task']);
                        $("#lead_auto_phone_number").val(responseText['data']['lead_type']['phone_number']);
                        $("#lead_auto_task_due_date_time").val(responseText['data']['due_date_time']);
                        $("#lead_auto_task_reminder").val(responseText['data']['reminder_text']);
                        $("#lead_auto_task_description").val(responseText['data']['description']);

                        $.ajax({
                            type: 'GET',
                            url: ajaxURLCallDetail + "?id=" + id + "&Call_type=is_auto_call",
                            success: function(responseText) {
                                if (responseText['status'] == 1) {
                                    $("#formLeadCall .loadingcls").hide();
                                    $("#lead_auto_call_lead_id").val(responseText['data']['lead_id']);
                                    $("#lead_auto_call_id").val(responseText['data']['id']);
                                    $("#lead_auto_call_schedule_date").val(responseText['data']['schedule_date']);

                                    var newOption = new Option(responseText['data']['schedule_time'], responseText['data']['schedule_time'], false, false);
                                    $('#lead_auto_call_schedule_time').append(newOption).trigger('change');
                                    $("#lead_auto_call_schedule_time").val("" + responseText['data']['schedule_time'] + "");
                                    $('#lead_auto_call_schedule_time').trigger('change');

                                    var newOption = new Option(responseText['data']['type']['text'], responseText['data']['type']['id'], false, false);
                                    $('#lead_auto_call_type_id').append(newOption).trigger('change');
                                    $("#lead_auto_call_type_id").val("" + responseText['data']['type']['id'] + "");
                                    $('#lead_auto_call_type_id').trigger('change');

                                    if (responseText['data']['is_closed'] == 0) {
                                        $('#lead_auto_call_closing_note_div').removeClass('d-none');
                                        $('#lead_auto_call_outcome_div').removeClass('d-none');
                                    }

                                    $("#lead_auto_call_move_to_close").val(1);
                                }
                            }
                        });

                    } else {
                        if (typeof responseText['data'] !== "undefined") {
                            var size = Object.keys(responseText['data']).length;
                            if (size > 0) {
                                for (var [key, value] of Object.entries(responseText['data'])) {
                                    toastr["error"](value);
                                }
                            }
                        } else {
                            toastr["error"](responseText['msg']);
                        }
                    }
                }
            });
        } else if(type == 'call'){
            $.ajax({
                type: 'GET',
                url: ajaxURLCallDetail + "?id=" + id,
                success: function(responseText) {
                    if (responseText['status'] == 1) {
                        TaskAndCallList(responseText['data']['lead_id'])
                        $("#formLeadCall .loadingcls").hide();
                        $("#lead_auto_call_lead_id").val(responseText['data']['lead_id']);
                        $("#lead_auto_call_id").val(responseText['data']['id']);
                        $("#lead_auto_call_schedule_date").val(responseText['data']['schedule_date']);

                        var newOption = new Option(responseText['data']['schedule_time'], responseText['data']['schedule_time'], false, false);
                        $('#lead_auto_call_schedule_time').append(newOption).trigger('change');
                        $("#lead_auto_call_schedule_time").val("" + responseText['data']['schedule_time'] + "");
                        $('#lead_auto_call_schedule_time').trigger('change');

                        var newOption = new Option(responseText['data']['type']['text'], responseText['data']['type']['id'], false, false);
                        $('#lead_auto_call_type_id').append(newOption).trigger('change');
                        $("#lead_auto_call_type_id").val("" + responseText['data']['type']['id'] + "");
                        $('#lead_auto_call_type_id').trigger('change');

                        if (responseText['data']['is_closed'] == 0) {
                            $('#lead_auto_call_closing_note_div').removeClass('d-none');
                            $('#lead_auto_call_outcome_div').removeClass('d-none');
                        }

                        $("#lead_auto_call_move_to_close").val(1);
                        $.ajax({
                            type: 'GET',
                            url: ajaxURLTaskDetail + "?id=" + responseText['data']['reference_id'],
                            success: function(responseText) {
                                if (responseText['status'] == 1) {
                                    $("#formLeadTask .loadingcls").hide();
                                    $("#lead_auto_call_lead_id").val(responseText['data']['lead_id']);
                                    $("#lead_auto_task_id").val(responseText['data']['id']);

                                    $('#lead_auto_task_assign_to').val(responseText['data']['assign_to']['text']);
                                    $("#lead_auto_task").val(responseText['data']['task']);
                                    $("#lead_auto_phone_number").val(responseText['data']['lead_type']['phone_number']);
                                    $("#lead_auto_task_due_date_time").val(responseText['data']['due_date_time']);
                                    $("#lead_auto_task_reminder").val(responseText['data']['reminder_text']);
                                    $("#lead_auto_task_description").val(responseText['data']['description']);

                                } else {
                                    if (typeof responseText['data'] !== "undefined") {
                                        var size = Object.keys(responseText['data']).length;
                                        if (size > 0) {
                                            for (var [key, value] of Object.entries(responseText['data'])) {
                                                toastr["error"](value);
                                            }
                                        }
                                    } else {
                                        toastr["error"](responseText['msg']);
                                    }
                                }
                            }
                        });
                    } else {
                        if (typeof responseText['data'] !== "undefined") {
                            var size = Object.keys(responseText['data']).length;
                            if (size > 0) {
                                for (var [key, value] of Object.entries(responseText['data'])) {
                                    toastr["error"](value);
                                }
                            }
                        } else {
                            toastr["error"](responseText['msg']);
                        }
                    }
                }
            });
        }
    }

    $("#lead_meeting_participants").select2({
        ajax: {
            url: ajaxURLSearchMeetingParticipants,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    'lead_id': function() {
                        return $("#lead_meeting_lead_id").val();
                    }
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: "Search For Participants",
        dropdownParent: $("#modalMeeting .modal-content")
    }).on('change', function() {});

    $("#lead_call_assign_user").select2({
        ajax: {
            url: ajaxURLSearchCallAssignTo,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    lead_id: function() {
                        return $("#lead_call_lead_id").val();
                    }
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for assign to',
        dropdownParent: $("#modalCall .modal-content")
    });

    $("#lead_meeting_title_id").select2({
        ajax: {
            url: ajaxURLSearchMeetingTitle,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for title',
        dropdownParent: $("#modalMeeting .modal-content")
    });

    $("#lead_task_assign_to").select2({
        ajax: {
            url: ajaxURLSearchTaskAssignTo,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    lead_id: function() {
                        return $("#lead_task_lead_id").val();
                    }
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for assign to',
        dropdownParent: $("#modalTask .modal-content")
    });

    $("#lead_task_due_time").select2({
        dropdownParent: $("#modalTask .modal-content")
    });

    $("#lead_task_reminder_time").select2({
        dropdownParent: $("#modalTask .modal-content")
    });

    $("#lead_meeting_time").select2({
        dropdownParent: $("#modalMeeting .modal-content")
    });

    $("#lead_meeting_reminder_time").select2({
        dropdownParent: $("#modalMeeting .modal-content")
    });

    $("#lead_call_schedule_time").select2({
        dropdownParent: $("#modalCall .modal-content")
    });

    $("#lead_call_reminder_time").select2({
        dropdownParent: $("#modalCall .modal-content")
    });

    $("#lead_meeting_end_time").select2({
        dropdownParent: $("#modalMeeting .modal-content")
    });

    $('#callFooter1 .save-btn').on('click', function() {
        $("#formLeadCall").submit();
    })

    $('#autoCallFooter1 .save-btn').on('click', function() {
        $("#formAutogenerateAction").submit();
    })

    $('#meetingFooter1 .save-btn').on('click', function() {
        $("#formLeadMeeting").submit();
    })

    $('#taskfooter1 .save-btn').on('click', function() {
        $("#formLeadTask").submit();
    })

    $("#lead_call_type_id").select2({
        ajax: {
            url: ajaxURLSearchCallType,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for type',
        dropdownParent: $("#modalCall .modal-content")
    }).on('change', function(e) {
        call_type = $('#lead_call_type_id').val();
        if (call_type == 1) {
            $('#call_notes_label').text('Call Notes')
            $('#lead_call_call_description').attr('placeholder', 'Call Notes');
            $('#lead_call_reminder_div').removeClass('d-none');
            $('#lead_call_status_div').addClass('d-none');
            $('#lead_call_closing_note_div').addClass('d-none');
            $('#lead_call_outcome_div').addClass('d-none');
        } else if (call_type == 2) {
            $('#call_notes_label').text('Call Discussion');
            $('#lead_call_call_description').attr('placeholder', 'Call Discussion');
            $('#lead_call_reminder_div').addClass('d-none');
            $('#lead_call_status_div').removeClass('d-none');
            $('#lead_call_closing_note_div').removeClass('d-none');
            $('#lead_call_outcome_div').removeClass('d-none');
        }
    });

    $("#lead_call_contact_name").select2({
        ajax: {
            url: ajaxURLSearchContact,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    lead_id: $('#lead_call_lead_id').val(),
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for Contact',
        dropdownParent: $("#modalCall .modal-content")
    });

    $("#lead_call_call_outcome").select2({
        ajax: {
            url: ajaxURLSearchCallOutcomeType,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for Outcome Type',
        dropdownParent: $("#modalCall .modal-content")
    }).on('change', function() {
        $.ajax({
            type: 'GET',
            url: ajaxURLOutComeTypeDetail + "?id=" + $(this).val(),
            success: function(responseText) {
                if (responseText['status'] == 1) {
                    if (responseText['data']['is_reschedule'] == 2) {
                        $('#call_received_but_reschedule_div').removeClass('d-none');
                    } else {
                        $('#call_received_but_reschedule_div').addClass('d-none');
                    }
                }
            }
        })
    });

    $("#lead_call_add_info").select2({
        ajax: {
            url: ajaxURLAdditionalInfo,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for Additional Info',
        dropdownParent: $("#modalCall .modal-content")
    }).on('change', function() {
        $.ajax({
            type: 'GET',
            url: ajaxURLAddInfoDetail + "?id=" + $(this).val(),
            success: function(responseText) {
                if (responseText['status'] == 1) {
                    if (responseText['data']['is_textfield'] == 1) {
                        $('#lead_call_add_info_text_div').removeClass('d-none');
                    } else {
                        $('#lead_call_add_info_text_div').addClass('d-none');
                    }
                }
            }
        })
    });

    $("#lead_meeting_type_id").select2({
        ajax: {
            url: ajaxURLSearchMeetingType,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for type',
        dropdownParent: $("#modalMeeting .modal-content")
    }).on('change', function(e) {
        meeting_type = $('#lead_meeting_type_id').val();
        if (meeting_type == 1) {
            $('#lead_meeting_description_label').text('Meeting Notes');
            $('#lead_meeting_description').attr('placeholder', 'Meeting Notes');
            $('#lead_meeting_is_notification_div').removeClass('d-none');
            $('#lead_meeting_closing_note_div').addClass('d-none');
            $('#lead_meeting_status_div').addClass('d-none');
            $('#lead_meeting_outcome_div').addClass('d-none');
        } else if (meeting_type == 2) {
            $('#lead_meeting_description_label').text('Meeting Discussion');
            $('#lead_meeting_description').attr('placeholder', 'Meeting Discussion');
            $('#lead_meeting_is_notification_div').addClass('d-none');
            $('#lead_meeting_status_div').removeClass('d-none');
            $('#lead_meeting_outcome_div').removeClass('d-none');
            $('#lead_meeting_closing_note_div').removeClass('d-none');
        }
    })

    $("#lead_meeting_meeting_outcome").select2({
        ajax: {
            url: ajaxURLSearchMeetingOutcomeType,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for Outcome Type',
        dropdownParent: $("#modalMeeting .modal-content")
    });

    $("#lead_meeting_status").select2({
        ajax: {
            url: ajaxURLSearchStatus,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    lead_id: $('#lead_meeting_lead_id').val(),
                    q: params.term, // search term
                    page: params.page,
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for Status',
        dropdownParent: $("#modalMeeting .modal-content")
    });

    $("#lead_call_status").select2({
        ajax: {
            url: ajaxURLSearchStatus,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    lead_id: $('#lead_call_lead_id').val(),
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for Status',
        dropdownParent: $("#modalCall .modal-content")
    });

    $("#lead_task_status").select2({
        ajax: {
            url: ajaxURLSearchStatus,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    lead_id: $('#lead_task_lead_id').val(),
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for Status',
        dropdownParent: $("#modalTask .modal-content")
    });

    $("#lead_task_task_outcome").select2({
        ajax: {
            url: ajaxURLSearchTaskOutcomeType,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    "is_auto_generate" : function(){
                        return $('#lead_task_auto_generate').val();
                    }
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for Outcome Type',
        dropdownParent: $("#modalTask .modal-content")
    }).on('change', function(){
        if($("#lead_task_task_outcome").val() == 101){
            $('#task_add_info_div').removeClass('d-none');
        } else if($("#lead_task_task_outcome").val() == 102) {
            $('#task_add_info_div').addClass('d-none');
        } else {
            $('#task_add_info_div').addClass('d-none');
        }
    });

    $("#lead_task_add_info").select2({
        ajax: {
            url: ajaxURLAdditionalInfo,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for Additional Info',
        dropdownParent: $("#modalTask .modal-content")
    }).on('change', function() {
        $.ajax({
            type: 'GET',
            url: ajaxURLAddInfoDetail + "?id=" + $(this).val(),
            success: function(responseText) {
                if (responseText['status'] == 1) {
                    if (responseText['data']['is_textfield'] == 1) {
                        $('#lead_task_add_info_text_div').removeClass('d-none');
                    } else {
                        $('#lead_task_add_info_text_div').addClass('d-none');
                    }
                }
            }
        })
    });

    $('#lead_call_move_to_close_btn').click(function() {
        $("#lead_call_move_to_close").val(1);
        $('#callFooter1 .save-btn').hide();
        $("#formLeadCall").submit();
    });

    $("#lead_task_move_to_close_btn").click(function() {
        $("#lead_task_move_to_close").val(1);
        $('#taskfooter1 .save-btn').hide();
        $('#modalTaskLabel').text('Close Task')
        $("#formLeadTask").submit();
    });

    $("#lead_meeting_move_to_close_btn").click(function() {
        $("#lead_meeting_move_to_close").val(1);
        $('#meetingFooter1 .save-btn').hide();
        $('#modalMeetingLabel').text('Close Meeting');
        $("#formLeadMeeting").submit();
    });

    $('#lead_task_reminder_date_time').append(new Option('15 Min Before', '1', false, false)).trigger('change');
    $("#lead_task_reminder_date_time").val('1');
    $('#lead_task_reminder_date_time').trigger('change');
    $("#lead_task_reminder_date_time").select2({
        ajax: {
            url: ajaxURLSearchReminderTimeSlot,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for Reminder Time',
        dropdownParent: $("#modalTask .modal-content")
    });

    $('#lead_meeting_reminder_date_time').append(new Option('15 Min Before', '1', false, false)).trigger('change');
    $("#lead_meeting_reminder_date_time").val('1');
    $('#lead_meeting_reminder_date_time').trigger('change');
    $("#lead_meeting_reminder_date_time").select2({
        ajax: {
            url: ajaxURLSearchReminderTimeSlot,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for Reminder Time',
        dropdownParent: $("#modalMeeting .modal-content")
    });

    $('#lead_call_reminder_date_time').append(new Option('15 Min Before', '1', false, false)).trigger('change');
    $("#lead_call_reminder_date_time").val('1');
    $('#lead_call_reminder_date_time').trigger('change');
    $("#lead_call_reminder_date_time").select2({
        ajax: {
            url: ajaxURLSearchReminderTimeSlot,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for Reminder Time',
        dropdownParent: $("#modalCall .modal-content")
    });

    $('#lead_meeting_interval_time').select2();

    $('#lead_meeting_participants, #lead_meeting_date, #lead_meeting_time, #lead_meeting_interval_time').on('change', function() {
        if (is_meeting_schedule == 0) {
            if(ScheduleTimeTable != null){
                ScheduleTimeTable.ajax.reload(null, false);
            }else{
                loadScheduleTimeTable();
            }
        }
    })
    
    function loadScheduleTimeTable(){
        ScheduleTimeTable = $('#ScheduleTable').DataTable({
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": [0]
            }, ],
            "sDom": "lrtip",
            "bInfo": false,
            "order": [
                [0, 'desc']
            ],
            "bPaginate": false,
            "processing": true,
            "serverSide": true,
            "bDestroy": true,
            "pageLength": 10,
            "ajax": {
                "url": ajaxURLFindMeetingTimes,
                "type": "POST",
                "data": {
                    "_token": csrfToken,
                    "lead_meeting_participants": function() {
                        return $('#lead_meeting_participants').val()
                    },
                    "location": function() {
                        return $('#lead_meeting_location').val()
                    },
                    "lead_meeting_start_date": function() {
                        return $('#lead_meeting_date').val()
                    },
                    "lead_meeting_start_time": function() {
                        return $('#lead_meeting_time').val()
                    },
                    "lead_meeting_interval_time": function() {
                        return $('#lead_meeting_interval_time').val()
                    },
                }
            },
            "aoColumns": [{
                "mData": "action",
            }, ],
        });
    
        ScheduleTimeTable.on('xhr', function() {
    
            var responseData = ScheduleTimeTable.ajax.json();
            if (responseData) {
                if (responseData['status'] != 0) {
                    $('#meeting_date_schedule').removeClass('d-none');
                    $('#suggested_time').removeClass('d-none');
                    $('#meeting_form').removeClass('col-12').addClass('col-8');
                }
            }
    
        });
    }



    $("#lead_auto_call_type_id").select2({
        ajax: {
            url: ajaxURLSearchCallType,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for type',
        dropdownParent: $("#modalAutoScheduleCall .modal-content")
    }).on('change', function(e) {
        call_type = $('#lead_auto_call_type_id').val();
        if (call_type == 1) {
            $('#call_notes_label').text('Call Notes')
            $('#lead_auto_call_call_description').attr('placeholder', 'Call Notes');
            $('#lead_auto_call_reminder_div').removeClass('d-none');
            $('#lead_auto_call_status_div').addClass('d-none');
            $('#lead_auto_call_closing_note_div').addClass('d-none');
            $('#lead_auto_call_outcome_div').addClass('d-none');
            $('#add_auto_info_div').addClass('d-none');
        } else if (call_type == 2) {
            $('#call_notes_label').text('Call Discussion');
            $('#lead_auto_call_call_description').attr('placeholder', 'Call Discussion');
            $('#lead_auto_call_reminder_div').addClass('d-none');
            $('#lead_auto_call_status_div').removeClass('d-none');
            $('#lead_auto_call_closing_note_div').removeClass('d-none');
            $('#lead_auto_call_outcome_div').removeClass('d-none');
        }
    });

    $("#lead_auto_call_contact_name").select2({
        ajax: {
            url: ajaxURLSearchContact,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    lead_id: $('#lead_call_lead_id').val(),
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for Contact',
        dropdownParent: $("#modalAutoScheduleCall .modal-content")
    });

    $("#lead_auto_call_assign_user").select2({
        ajax: {
            url: ajaxURLSearchCallAssignTo,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    lead_id: function() {
                        return $("#lead_call_lead_id").val();
                    }
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for assign to',
        dropdownParent: $("#modalAutoScheduleCall .modal-content")
    });

    $("#lead_auto_call_schedule_time").select2({
        dropdownParent: $("#modalAutoScheduleCall .modal-content")
    });

    $("#lead_auto_call_outcome").select2({
        ajax: {
            url: ajaxURLSearchCallOutcomeType,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for Outcome Type',
        dropdownParent: $("#modalAutoScheduleCall .modal-content")
    }).on('change', function() {
        $.ajax({
            type: 'GET',
            url: ajaxURLOutComeTypeDetail + "?id=" + $(this).val(),
            success: function(responseText) {
                if (responseText['status'] == 1) {
                    if (responseText['data']['is_reschedule'] == 2) {
                        $('#auto_call_received_but_reschedule_div').removeClass('d-none');
                    } else {
                        $('#auto_call_received_but_reschedule_div').addClass('d-none');
                    }

                    // $("#lead_auto_call_add_info_ele").removeAttr('required');
                    // $("#lead_auto_call_add_info_arc").removeAttr('required');

                    if (responseText['data']['is_reschedule'] == 0) {
                        $('#add_auto_info_div').removeClass('d-none');

                        // $('#lead_auto_call_add_info_ele').prop('required', true);
                        // $('#lead_auto_call_add_info_arc').prop('required', true);

                    } else {
                        $('#add_auto_info_div').addClass('d-none');
                    }
                }
            }
        })
    });

    $("#lead_auto_call_add_info").select2({
        ajax: {
            url: ajaxURLAdditionalInfo,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for Additional Info',
        dropdownParent: $("#modalAutoScheduleCall .modal-content")
    }).on('change', function() {
        $.ajax({
            type: 'GET',
            url: ajaxURLAddInfoDetail + "?id=" + $(this).val(),
            success: function(responseText) {
                if (responseText['status'] == 1) {
                    if (responseText['data']['is_textfield'] == 1) {
                        $('#lead_auto_call_add_info_text_div').removeClass('d-none');
                    } else {
                        $('#lead_auto_call_add_info_text_div').addClass('d-none');
                    }
                }
            }
        })
    });

    function TaskDetail(id){
        $('#modalTaskView').modal('show');
        $.ajax({
            type: 'GET',
            url: ajaxURLAutoTaskDetail + "?id=" + id,
            success: function(resultData) {
                if (resultData['status'] == 1) {
                    var data = resultData['data'];
                    $('#task_title').val(data['task']);

                    if(data['is_closed'] == 0){
                        $('#task_status').val('Open');
                    } else {
                        $('#task_status').val('Close');
                    }

                    $('#task_lead_detail').val(data['lead_detail']);
                    $('#task_created_by').val(data['created_by']);
                    $('#task_created_at').val(data['created_at']);
                    $('#task_due_date_time').val(data['due_date_time']);
                    $('#task_description').val(data['description']);
                    $('#task_close_date_time').val(data['closed_date_time']);
                    $('#task_close_note').val(data['close_note']);
                    $('#task_outcome_type').val(data['outcome_type']);
                    $('#task_architect').val(data['architect_name']);
                    $('#task_electrician').val(data['electrician_name']);
                    $('#task_additional_info').val(data['additional_info']);
                    $('#task_additional_info_text').val(data['additional_info_text']);
                }
            }
        });
    }

    function CallDetail(id){
        $('#modalCallView').modal('show');
        $.ajax({
            type: 'GET',
            url: ajaxURLAutoCallDetail + "?id=" + id,
            success: function(resultData) {
                if (resultData['status'] == 1) {
                    var data = resultData['data'];
                    if(data['is_closed'] == 0){
                        $('#call_status').val('Open');
                    } else {
                        $('#call_status').val('Close');
                    }
                    $('#call_contact').val(data['contact_name']);
                    $('#call_purpose').val(data['purpose']);
                    $('#call_schedule').val(data['call_schedule']);
                    $('#call_lead_detail').val(data['lead_detail']);
                    $('#call_created_by').val(data['created_by']);
                    $('#call_created_at').val(data['created_at']);
                    $('#call_description').val(data['description']);
                    $('#call_close_date_time').val(data['closed_date_time']);
                    $('#call_close_note').val(data['close_note']);
                    $('#call_outcome_type').val(data['outcome_type']);
                    $('#call_reference').val(data['reference_type']);
                    $('#call_architect').val(data['architect_name']);
                    $('#call_electrician').val(data['electrician_name']);
                    $('#call_additional_info').val(data['additional_info']);
                    $('#call_additional_info_text').val(data['additional_info_text']);
                }
            }
        });
    }

    function TaskAndCallList(id){
        $.ajax({
            type: 'GET',
            url: ajaxURLAutoTaskAndCallList + "?lead_id=" + id,
            success: function(resultData) {
                if (resultData['status'] == 1) {
                    var data = resultData['data'];
                    $('#leadCallAndTaskListBody').html(data)
                }
            }
        });
    }

    function ShowBrandWiseDiscount(group_id){
        $.ajax({
            type: 'GET',
            url: ajaxURLQuotationBrandList + "?group_id=" + group_id,
            success: function(resultData) {
                if (resultData['status'] == 1) {
                    $("#modalQuotationBrandView").modal('show');
                    var data = resultData['data'];
                    $('#BrandListPreview').html(resultData['data'])
                }
            }
        });
    }

    function changeDiscount(inputId,discount,maxDiscount){
        var inputDis = $('#'+inputId).val();
        if(parseInt(inputDis)  > parseInt(maxDiscount)){
            $('#'+inputId).val(discount);
        }
    }


    function ApprovedAndRejectDiscount(inputId,quot_req_line_id, group_id, type){
        $.ajax({
            type: 'POST',
            url: ajaxURLQuotDiscountApprovedOrReject,
            data:{
                "_token": csrfToken,
                'group_id' : group_id,
                'type' : type,
                'discount' : $('#'+inputId).val(),
                'quot_req_line_id' : quot_req_line_id,
            },
            success: function(resultData) {
                if (resultData['status'] == 1) {
                    toastr["success"](resultData['msg']);
                    ShowBrandWiseDiscount(group_id);
                } else {
                    toastr["error"](resultData['msg']);
                }
            }
        });
    }
</script>
