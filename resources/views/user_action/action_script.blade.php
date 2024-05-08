<script>
    var ajaxURLSearchCallType = "{{ route('user.action.search.call.type') }}";
    var ajaxURLSearchCallOutcomeType = "{{ route('user.action.search.call.outcome.type') }}";
    var ajaxURLSearchMeetingType = "{{ route('user.action.search.meeting.type') }}";
    var ajaxURLSearchContact = "{{ route('user.action.search.contact') }}";
    var ajaxURLSearchMeetingTitle = "{{ route('user.action.search.meeting.title') }}";
    var ajaxURLSearchMeetingOutcomeType = "{{ route('user.action.search.meeting.outcome.type') }}";
    var ajaxURLSearchTaskAssignTo = "{{ route('user.action.search.task.assign') }}";
    var ajaxURLSearchMeetingParticipants = "{{ route('user.action.search.meeting.participants') }}";
    var ajaxURLSearchTaskOutcomeType = "{{ route('user.action.search.task.outcome.type') }}";
    var ajaxURLSearchContactTag = "{{ route('crm.lead.search.contact.tag') }}";
    var ajaxURLSearchFileTag = "{{ route('crm.lead.search.file.tag') }}";
    var ajaxURLCallDetail = "{{ route('user.action.call.detail') }}";
    var ajaxURLTaskDetail = "{{ route('user.action.task.detail') }}";
    var ajaxURLMeetingDetail = "{{ route('user.action.meeting.detail') }}";
    var ajaxURLOpenActionAll = "{{ route('user.action.open.action.all') }}";
    var ajaxURLCloseActionALL = "{{ route('user.action.close.action.all') }}";
    var ajaxURLFileALL = "{{ route('user.action.files.all') }}";
    var ajaxURLContactALL = "{{ route('user.action.contact.all') }}";
    var ajaxURLUpdateALL = "{{ route('user.action.notes.all') }}";
    var ajaxURLDeleteFile = "{{ route('user.action.file.delete') }}";
    var ajaxURContactDetail = "{{ route('user.action.contact.detail') }}";
    var ajaxURLSearchReminderTimeSlot = "{{ route('search.reminder.time.slot') }}";
    var ajaxURLUserStatusChange = "{{ route('user.status.change') }}";

    $(function() {
        $('.datetimepicker').datetimepicker({
            format: 'dd:mm:yyyy HH:ss a'
        });
    });

    $(document).ready(function() {
        var options = {
            beforeSubmit: showRequest,
            success: showResponse
        };

        $('#formUser').ajaxForm(options);
        $('#formElectricianUser').ajaxForm(options);
        $('#modalUserContact').ajaxForm(options);
        $('#formUserFile').ajaxForm(options);
        $("#formUserCall").ajaxForm(options);
        $("#formUserTask").ajaxForm(options);
        $("#formUserMeeting").ajaxForm(options);
        $("#formLeadQuotation").ajaxForm(options);
    });

    function showRequest(formData, jqForm, options) {

        var queryString = $.param(formData);

        $(".save-btn").html("Saving...");
        $(".save-btn").prop("disabled", true);

        $("#btnSaveFinal").html("Saving...");
        $("#btnSaveFinal").prop('disabled', true);
        return true;
    }

    function showResponse(responseText, statusText, xhr, $form) {

        $(".save-btn").html("Save");
        $(".save-btn").prop("disabled", false);
        console.log($form[0]['id']);
        $("#btnSaveFinal").prop('disabled', false);
        $("#btnSaveFinal").html("Save");

        if ($form[0]['id'] == "formUser") {
            if (responseText['status'] == 1) {
                toastr["success"](responseText['msg']);
                resetInputForm();
                getDataDetail(responseText['user_id']);
                $("#modalUser").modal('hide');


            } else if (responseText['status'] == 0) {

                    toastr["error"](responseText['msg']);

            }
        } else if ($form[0]['id'] == "formElectricianUser") {
            if (responseText['status'] == 1) {
                toastr["success"](responseText['msg']);
                resetInputForm();
                $("#modalElectricianUser").modal('hide');
            } else if (responseText['status'] == 0) {
                toastr["error"](responseText['msg']);
            }

        } else if ($form[0]['id'] == "formUserContact") {
            console.log($form);
            if (responseText['status'] == 1) {
                $('#contact_loader').show();
                toastr["success"](responseText['msg']);
                $('#formUserContact').trigger("reset");
                $("#modalUserContact").modal('hide');

                $.ajax({
                    type: 'GET',
                    url: ajaxURLContactALL + "?user_id=" + responseText['id'] + "&islimit=1",
                    success: function(resultData) {
                        if (resultData['status'] == 1) {
                            $("#tab_contact").html(resultData['view']);
                            $('#contact_loader').hide();
                        }
                    }
                });
            } else if (responseText['status'] == 0) {

                    toastr["error"](responseText['msg']);
                $('#contact_loader').hide();

            }

        } else if ($form[0]['id'] == "formUserFile") {

            if (responseText['status'] == 1) {
                $('#file_loader').show();
                toastr["success"](responseText['msg']);
                $('#formUserFile').trigger("reset");
                $("#modalFile").modal('hide');


                $.ajax({
                    type: 'GET',
                    url: ajaxURLFileALL + "?user_id=" + responseText['id'] + "&islimit=1",
                    success: function(resultData) {
                        if (resultData['status'] == 1) {
                            $("#tab_files").html(resultData['view']);
                            $('#file_loader').hide();
                        }
                    }
                });

            } else if (responseText['status'] == 0) {

                    toastr["error"](responseText['msg']);
                $('#file_loader').hide();

            }

        } else if ($form[0]['id'] == "formUserCall") {

            if (responseText['status'] == 1) {
                toastr["success"](responseText['msg']);
                $('#formUserCall').trigger("reset");
                $("#modalCall").modal('hide');
                $("#call_move_to_close").val(0);
                $('#open_action_loader').show();
                $('#close_action_loader').show();
                $.ajax({
                    type: 'GET',
                    url: ajaxURLOpenActionAll + "?user_id=" + responseText['id'],
                    success: function(resultData) {
                        if (resultData['status'] == 1) {
                            $("#tab_action").html(resultData['view']);
                            $('#open_action_loader').hide();
                        }
                    }
                });

                $.ajax({
                    type: 'GET',
                    url: ajaxURLCloseActionALL + "?user_id=" + responseText['id'],
                    success: function(resultData) {
                        if (resultData['status'] == 1) {
                            $("#tab_close_action").html(resultData['view']);
                            $('#close_action_loader').hide();
                        }
                    }
                });

                $('#note_loader').show();
                $.ajax({
                    type: 'GET',
                    url: ajaxURLUpdateALL + "?user_id=" + responseText['id'] + "&islimit=1",
                    success: function(resultData) {
                        if (resultData['status'] == 1) {
                            $("#tab_notes").html(resultData['view']);
                            $('#note_loader').hide();
                        }
                    }
                });

                if (responseText['ask_for_status_change'] == 1) {
                    $("#modalStatus").modal('show');
                    $("#lead_status_lead_id").val(responseText['id']);
                    for (var i = 0; i < responseText['status_array'].length; i++) {
                        var newOption = new Option(responseText['status_array'][i]['name'], responseText['status_array']
                            [i]['id'], false, false);
                        $('#lead_status_new').append(newOption).trigger('change');
                    }

                    // $("#formLeadStatusChange .loadingcls").hide();



                }



            } else if (responseText['status'] == 0) {

               
                    toastr["error"](responseText['msg']);
                $('#close_action_loader').hide();
                $('#open_action_loader').hide();
            }

        } else if ($form[0]['id'] == "formUserTask") {

            if (responseText['status'] == 1) {
                toastr["success"](responseText['msg']);
                $('#formTaskCall').trigger("reset");
                $("#modalTask").modal('hide');
                $("#task_move_to_close").val(0);
                $("#task_move_to_close_btn").hide();
                $('#modalTaskLabel').text('Schedule Task');
                $('#taskfooter1 .save-btn').show();
                $('#open_action_loader').show();
                $('#close_action_loader').show();
                $.ajax({
                    type: 'GET',
                    url: ajaxURLOpenActionAll + "?user_id=" + responseText['id'],
                    success: function(resultData) {
                        if (resultData['status'] == 1) {
                            $("#tab_action").html(resultData['view']);
                            $('#open_action_loader').hide();
                        }
                    }
                });


                $.ajax({
                    type: 'GET',
                    url: ajaxURLCloseActionALL + "?user_id=" + responseText['id'],
                    success: function(resultData) {
                        if (resultData['status'] == 1) {
                            $("#tab_close_action").html(resultData['view']);
                            $('#close_action_loader').hide();
                        }
                    }
                });
                $('#note_loader').show();
                $.ajax({
                    type: 'GET',
                    url: ajaxURLUpdateALL + "?user_id=" + responseText['id'] + "&islimit=1",
                    success: function(resultData) {
                        if (resultData['status'] == 1) {
                            $("#tab_notes").html(resultData['view']);
                            $('#note_loader').hide();
                        }
                    }
                });


            } else if (responseText['status'] == 0) {

                
                    toastr["error"](responseText['msg']);
                $('#close_action_loader').hide();
                $('#open_action_loader').hide();
            }

        } else if ($form[0]['id'] == "formUserMeeting") {
            console.log($form);
            if (responseText['status'] == 1) {
                toastr["success"](responseText['msg']);
                $('#formUserMeeting').trigger("reset");
                $("#modalMeeting").modal('hide');
                $('#open_action_loader').show();
                $('#close_action_loader').show();
                $.ajax({
                    type: 'GET',
                    url: ajaxURLOpenActionAll + "?user_id=" + responseText['id'],
                    success: function(resultData) {
                        if (resultData['status'] == 1) {
                            $("#tab_action").html(resultData['view']);
                            $('#open_action_loader').hide();
                        }
                    }
                });

                $.ajax({
                    type: 'GET',
                    url: ajaxURLCloseActionALL + "?user_id=" + responseText['id'],
                    success: function(resultData) {
                        if (resultData['status'] == 1) {
                            $("#tab_close_action").html(resultData['view']);
                            $('#close_action_loader').hide();
                        }
                    }
                });
                $('#note_loader').show();
                $.ajax({
                    type: 'GET',
                    url: ajaxURLUpdateALL + "?user_id=" + responseText['id'] + "&islimit=1",
                    success: function(resultData) {
                        if (resultData['status'] == 1) {
                            $("#tab_notes").html(resultData['view']);
                            $('#note_loader').hide();
                        }
                    }
                });


            } else if (responseText['status'] == 0) {

                
                    toastr["error"](responseText['msg']);
                $('#close_action_loader').hide();
                $('#open_action_loader').hide();
            }

        } else if ($form[0]['id'] == "formLeadQuotation") {

            if (responseText['status'] == 1) {
                toastr["success"](responseText['msg']);
                $('#formLeadQuotation').trigger("reset");
                $("#modalQuotation").modal('hide');
                getDataDetail(responseText['id']);


            } else if (responseText['status'] == 0) {
               
                    toastr["error"](responseText['msg']);

            }
        }

    }

    function addUserContactModal(id) {
        $("#modalUserContact").modal('show');
        $("#formUserContact .loadingcls").hide();
        $('#formUserContact').trigger("reset");
        $("#contact_user_id").val(id);
        $("#contact_id").val(0);
        $('#contact_tag_id').empty().trigger('change');
    }

    function addLeadFileModal(id) {
        $("#modalFile").modal('show');
        $("#formUserFile .loadingcls").hide();
        $("#file_user_id").val(id);
    }

    function viewCall(id, type) {
        $('#hidden_action_id').val(id + '-call');
        $("#modalCall").modal('show');
        $("#call_type_id").empty().trigger('change');
        $("#call_contact_name").empty().trigger('change');
        $('#formUserCall').trigger("reset");

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

                    console.log(responseText['data']);

                    $("#formUserCall .loadingcls").hide();
                    $("#call_user_id").val(responseText['data']['user_id']);
                    $("#call_id").val(responseText['data']['id']);

                    $("#call_schedule_date").val(responseText['data']['schedule_date']);

                    var newOption = new Option(responseText['data']['schedule_time'], responseText['data'][
                        'schedule_time'
                    ], false, false);
                    $('#call_schedule_time').append(newOption).trigger('change');
                    $("#call_schedule_time").val("" + responseText['data']['schedule_time'] + "");
                    $('#call_schedule_time').trigger('change');

                    var newOption = new Option(responseText['data']['reminder_text'], responseText['data'][
                        'reminder_id'
                    ], false, false);
                    $('#call_reminder_date_time').append(newOption).trigger('change');
                    $("#call_reminder_date_time").val("" + responseText['data']['reminder_id'] + "");
                    $('#call_reminder_date_time').trigger('change');

                    $("#call_description").val(responseText['data']['description']);

                    $("#call_purpose").val(responseText['data']['purpose']);

                    var newOption = new Option(responseText['data']['type']['text'], responseText['data'][
                        'type'
                    ]['id'], false, false);
                    $('#call_type_id').append(newOption).trigger('change');
                    $("#call_type_id").val("" + responseText['data']['type']['id'] + "");
                    $('#call_type_id').trigger('change');



                    var newOption = new Option(responseText['data']['contact_name']['text'], responseText[
                        'data']['contact_name']['id'], false, false);
                    $('#call_contact_name').append(newOption).trigger('change');
                    $("#call_contact_name").val("" + responseText['data']['contact_name']['id'] + "");
                    $('#call_contact_name').trigger('change');


                    $("#call_move_to_close").val(0);
                    if (responseText['data']['is_closed'] == 0) {
                        $("#call_move_to_close_btn").show();
                        $('#call_purpose_div').removeClass('d-none');
                        $('#call_call_outcome_div').removeClass('d-none');
                        $('#call_closing_note_div').removeClass('d-none');
                        $('#modalCallLabel').text('Close Call');
                        $("#callFooter1 .save-btn").hide();
                        $('#callFooter1 .save-btn').addClass('d-none');

                        $('#call_type_div, #call_contact_name_div, #call_call_schedule_div, #call_reminder_div, #call_purpose_div, #call_notes_div, #select2-call_type_id-container, #call_call_schedule, #select2-call_contact_name-container, #call_description, #call_purpose, #call_reminder, #call_schedule_date, #select2-call_schedule_time-container, #select2-call_reminder_date_time-container')
                            .addClass('bg-light')
                        $('#call_call_schedule, #call_reminder, #call_description')
                            .attr('readonly', true);
                        $('#pointer_event_call_type, #pointer_event_call_contact_name, #call_call_schedule_div, #call_reminder_div')
                            .addClass('pe-none');
                    } else {
                        var newOption = new Option(responseText['data']['outcome_type']['text'],
                            responseText['data']['outcome_type']['id'], false, false);
                        $('#call_call_outcome_div').append(newOption).trigger('change');
                        $("#call_call_outcome_div").val("" + responseText['data']['outcome_type']['id'] +
                            "");
                        $('#call_call_outcome_div').trigger('change');
                        $("#call_move_to_close_btn").hide();
                        // $('#callFooter1 .save-btn').removeClass('d-none');
                        $("#callFooter1 .save-btn").show();
                        $('#modalCallLabel').text('Call');
                    }


                    // $(".callFooter").hide();
                    //$("#callFooter2").show();

                } else {

                    
                        toastr["error"](responseText['msg']);

                }
            }
        });



    }

    function viewMeeting(id, type) {

        $('#hidden_action_id').val(id + '-meeting');
        if (type == 'close') {
            $('#modalMeetingLabel').text('Close Meeting');
            $('#meetingFooter1 .save-btn').addClass('d-none');
        } else if (type == 'open') {
            $('#meetingFooter1 .save-btn').removeClass('d-none');
        }
        $("#modalMeeting").modal('show');



        $("#meeting_title_id").empty().trigger('change');
        $("#meeting_participants").empty().trigger('change');
        $('#formUserMeeting').trigger("reset");
        var status = $(".status-active-class").text();
        $.ajax({
            type: 'GET',
            url: ajaxURLMeetingDetail + "?id=" + id,
            success: function(responseText) {
                if (responseText['status'] == 1) {
                    $("#formUserMeeting .loadingcls").hide();
                    $("#meeting_user_id").val(responseText['data']['user_id']);
                    $("#meeting_id").val(responseText['data']['id']);
                    $("#meeting_location").val(responseText['data']['location']);
                    $("#meeting_description").val(responseText['data']['description']);
                    // SIMPLE


                    $("#meeting_date").val(responseText['data']['meeting_date']);

                    var newOption = new Option(responseText['data']['meeting_time'], responseText['data']['meeting_time'], false, false);
                    $('#meeting_time').append(newOption).trigger('change');
                    $("#meeting_time").val("" + responseText['data']['meeting_time'] + "");
                    $('#meeting_time').trigger('change');

                    var newOption = new Option(responseText['data']['reminder_text'], responseText['data']['reminder_id'], false, false);
                    $('#meeting_reminder_date_time').append(newOption).trigger('change');
                    $("#meeting_reminder_date_time").val("" + responseText['data']['reminder_id'] + "");
                    $('#meeting_reminder_date_time').trigger('change');

                    var newOption = new Option(responseText['data']['title']['text'], responseText['data'][
                        'title'
                    ]['id'], false, false);
                    $('#meeting_title_id').append(newOption).trigger('change');
                    $("#meeting_title_id").val("" + responseText['data']['title']['id'] + "");
                    $('#meeting_title_id').trigger('change');

                    var newOption = new Option(responseText['data']['type']['text'], responseText['data'][
                        'type'
                    ]['id'], false, false);
                    $('#meeting_type_id').append(newOption).trigger('change');
                    $("#meeting_type_id").val("" + responseText['data']['type']['id'] + "");
                    $('#meeting_type_id').trigger('change');


                    $("#meeting_participants").empty().trigger('change');
                    var selectedParticipant = [];
                    for (var i = 0; i < responseText['data']['user_meeting_participant'].length; i++) {
                        selectedParticipant.push('' + responseText['data']['user_meeting_participant'][i][
                            'id'
                        ] + '');
                        var newOption = new Option(responseText['data']['user_meeting_participant'][i][
                            'text'
                        ], responseText['data']['user_meeting_participant'][i]['id'], false, false);
                        $('#meeting_participants').append(newOption).trigger('change');
                    }
                    $("#meeting_participants").val(selectedParticipant).change();
                    $("#meeting_move_to_close").val(0);
                    if (responseText['data']['is_closed'] == 0) {
                        $("#meeting_move_to_close_btn").show();
                        $('#modalMeetingLabel').text('Close Meeting');

                        $('#meeting_closing_note_div').removeClass('d-none');
                        $('#meeting_outcome_div').removeClass('d-none');

                        $("#meetingFooter1 .save-btn").hide();

                        $('#meeting_title_div, #meeting_type_div, #meeting_location_div, #meeting_date_time_div, #meeting_is_notification_div, #meeting_participants_div, #meeting_note_div, #select2-meeting_title_id-container, #select2-meeting_type_id-container, #meeting_location, #meeting_date_time, #meeting_reminder_id, #meeting_description, #select2-meeting_reminder_date_time-container, #meeting_date, #select2-meeting_time-container')
                            .addClass('bg-light')
                        $('#meeting_participants_div .select2-selection--multiple').addClass('bg-light');
                        $('#meeting_location, #meeting_date_time, #meeting_reminder_id, #meeting_description')
                            .attr('readonly', true);
                        $('#pointer_event_meeting_participants, #pointer_event_meeting_title, #pointer_event_meeting_type, #meeting_date_time_div, #meeting_is_notification_div')
                            .addClass('pe-none');
                    } else {
                        $("#meeting_move_to_close_btn").hide();
                        $("#meetingFooter1 .save-btn").show();
                    }
                    // $(".callFooter").hide();
                    //$("#callFooter2").show();
                } else {

                        toastr["error"](responseText['msg']);

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
        $("#task_assign_to").empty().trigger('change');
        $('#formUserTask').trigger("reset");
        var status = $(".status-active-class").text();
        $.ajax({
            type: 'GET',
            url: ajaxURLTaskDetail + "?id=" + id,
            success: function(responseText) {
                if (responseText['status'] == 1) {
                    $("#formUserTask .loadingcls").hide();
                    $("#task_user_id").val(responseText['data']['user_id']);
                    $("#task_id").val(responseText['data']['id']);

                    $("#user_task").val(responseText['data']['task']);

                    $("#task_due_date").val(responseText['data']['due_date']);

                    var newOption = new Option(responseText['data']['due_time'], responseText['data']['due_time'], false, false);
                    $('#task_due_time').append(newOption).trigger('change');
                    $("#task_due_time").val("" + responseText['data']['due_time'] + "");
                    $('#task_due_time').trigger('change');

                    var newOption = new Option(responseText['data']['reminder_text'], responseText['data']['reminder_id'], false, false);
                    $('#task_reminder_date_time').append(newOption).trigger('change');
                    $("#task_reminder_date_time").val("" + responseText['data']['reminder_id'] + "");
                    $('#task_reminder_date_time').trigger('change');

                    $("#task_description").val(responseText['data']['description']);

                    var newOption = new Option(responseText['data']['assign_to']['text'], responseText[
                        'data']['assign_to']['id'], false, false);
                    $('#task_assign_to').append(newOption).trigger('change');
                    $("#task_assign_to").val("" + responseText['data']['assign_to']['id'] + "");
                    $('#task_assign_to').trigger('change');

                    $("#task_move_to_close").val(0);


                    if (responseText['data']['is_closed'] == 0) {
                        $("#task_move_to_close_btn").show();
                        $('#modalTaskLabel').text('Close Task');
                        $('#closing_note_div').removeClass('d-none');
                        $('#task_outcome_div').removeClass('d-none');
                        $('#taskfooter1 .save-btn').hide();

                        $('#task_assign_to_div, #task_div, #task_due_date_time_div, #task_reminder_div, #task_description_div, #select2-task_assign_to-container, #user_task_div, #task_due_date, #select2-task_reminder_date_time-container, #task_description, #select2-task_due_time-container')
                            .addClass('bg-light')

                        $('#user_task, #task_due_date_time, #task_reminder_id, #task_description')
                            .attr('readonly', true);
                        $('#pointer_event_assign_to, #task_due_date_time_div, #task_reminder_div').addClass(
                            'pe-none');
                    } else {
                        $("#task_move_to_close_btn").hide();
                        $('#taskfooter1 .save-btn').show();
                    }


                    // $(".callFooter").hide();
                    //$("#callFooter2").show();

                } else {

                    
                        toastr["error"](responseText['msg']);

                }
            }
        });



    }

    $("#contact_tag_id").select2({
        ajax: {
            url: ajaxURLSearchContactTag,
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
        placeholder: 'Search for tag',
        dropdownParent: $("#modalUserContact .modal-content")
    });

    $("#meeting_participants").select2({
        ajax: {
            url: ajaxURLSearchMeetingParticipants,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    'user_id': function() {
                        return $("#meeting_user_id").val();
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
    });

    $("#meeting_title_id").select2({
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

    $("#task_assign_to").select2({
        ajax: {
            url: ajaxURLSearchTaskAssignTo,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    user_id: function() {
                        return $("#task_user_id").val();
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

    // $('#callFooter1 .save-btn').on('click', function() {
    //     $("#formUserCall").submit();
    // })

    // $('#meetingFooter1 .save-btn').on('click', function() {
    //     $("#formUserMeeting").submit();
    // })

    // $('#taskfooter1 .save-btn').on('click', function() {
    //     $("#formUserTask").submit();
    // })

    $("#call_type_id").select2({
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
        call_type = $('#call_type_id').val();
        if (call_type == 1) {
            $('#call_notes_label').text('Call Notes')
            $('#call_description').attr('placeholder', 'Call Notes');
            $('#call_reminder_div').removeClass('d-none');
            $('#call_call_outcome_div').addClass('d-none');
        } else if (call_type == 2) {
            $('#call_notes_label').text('Call Discussion');
            $('#call_description').attr('placeholder', 'Call Discussion');
            $('#call_reminder_div').addClass('d-none');
            $('#call_call_outcome_div').removeClass('d-none');
        }
    });

    $("#call_contact_name").select2({
        ajax: {
            url: ajaxURLSearchContact,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "user_id": $('#call_user_id').val(),
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

    $("#call_call_outcome").select2({
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
    });

    $("#meeting_type_id").select2({
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
        meeting_type = $('#meeting_type_id').val();
        if (meeting_type == 1) {
            $('#meeting_description_label').text('Meeting Notes');
            $('#meeting_description').attr('placeholder', 'Meeting Notes');
            $('#meeting_is_notification_div').removeClass('d-none');
            $('#meeting_status_div').addClass('d-none');
            $('#meeting_outcome_div').addClass('d-none');
        } else if (meeting_type == 2) {
            $('#meeting_description_label').text('Meeting Discussion');
            $('#meeting_description').attr('placeholder', 'Meeting Discussion');
            $('#meeting_is_notification_div').addClass('d-none');
            $('#meeting_status_div').removeClass('d-none');
            $('#meeting_outcome_div').removeClass('d-none');
        }
    })

    $("#meeting_outcome").select2({
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


    $("#task_outcome").select2({
        ajax: {
            url: ajaxURLSearchTaskOutcomeType,
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
        dropdownParent: $("#modalTask .modal-content")
    });

    $('#call_move_to_close_btn').click(function() {
        $("#call_move_to_close").val(1);
        $('#callFooter1 .save-btn').hide();
        $("#formUserCall").submit();
    });

    $("#task_move_to_close_btn").click(function() {
        $("#task_move_to_close").val(1);
        $('#taskfooter1 .save-btn').hide();
        $('#modalTaskLabel').text('Close Task')
        $("#formUserTask").submit();
    });

    $("#meeting_move_to_close_btn").click(function() {
        $("#meeting_move_to_close").val(1);
        $('#meetingFooter1 .save-btn').hide();
        $('#modalMeetingLabel').text('Close Meeting');
        $("#formUserMeeting").submit();
    });

    $("#file_tag_id").select2({
        ajax: {
            url: ajaxURLSearchFileTag,
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
        placeholder: 'Search for tag',
        dropdownParent: $("#modalFile .modal-content")
    });

    function deleteLeadFile(id) {

        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: !0,
            confirmButtonText: "Yes, mark as delete !",
            cancelButtonText: "No, delete!",
            confirmButtonClass: "btn btn-success mt-2",
            cancelButtonClass: "btn btn-danger ms-2 mt-2",
            loaderHtml: "<i class='bx bx-hourglass bx-spin font-size-16 align-middle me-2'></i> Loading",
            customClass: {
                confirmButton: 'btn btn-primary btn-lg',
                cancelButton: 'btn btn-danger btn-lg',
                loader: 'custom-loader'
            },
            buttonsStyling: !1,
            preConfirm: function(n) {
                return new Promise(function(t, e) {

                    Swal.showLoading()


                    $.ajax({
                        type: 'GET',
                        url: ajaxURLDeleteFile + "?id=" + id,
                        success: function(resultData) {

                            if (resultData['status'] == 1) {

                                $("#tr_file_" + id).remove();

                                t()



                            }




                        }
                    });



                })
            },
        }).then(function(t) {

            if (t.value === true) {



                Swal.fire({
                    title: "Mark as deleted!",
                    text: "Your record has been updated.",
                    icon: "success"
                });


            }

        });

    }

    function editLeadContact(id) {
        $("#modalUserContact").modal('show');
        $("#formUserContact .loadingcls").hide();
        $.ajax({
            type: 'GET',
            url: ajaxURContactDetail + "?id=" + id,
            success: function(responseText) {
                if (responseText['status'] == 1) {
                    var newOption = new Option(responseText['data']['type']['text'], responseText['data'][
                        'type'
                    ]['id'], false, false);
                    $('#contact_tag_id').append(newOption).trigger('change');
                    $("#contact_tag_id").val("" + responseText['data']['type']['id'] + "");
                    $('#contact_tag_id').trigger('change');

                    $("#contact_id").val(responseText['data']['id']);
                    $("#contact_user_id").val(responseText['data']['user_id']);
                    $("#contact_first_name").val(responseText['data']['first_name']);
                    $("#contact_last_name").val(responseText['data']['last_name']);
                    $("#contact_phone_number").val(responseText['data']['phone_number']);
                    $("#contact_alernate_phone_number").val(responseText['data']['alernate_phone_number']);
                    $("#contact_email").val(responseText['data']['email']);
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

    function resetInputForm() {

        $("#formUser").removeClass('was-validated');
        $('#formUser').trigger("reset");
        $('.nav a:first').tab('show');
        $("#user_status").select2("val", "1");
        $("#user_country_id").select2("val", "1");
        $("#user_state_id").empty().trigger('change');
        $("#user_city_id").empty().trigger('change');
        $("#architect_sale_person_id").empty().trigger('change');
        $("#architect_visiting_card_file").html("");
        $("#architect_aadhar_card_file").html("");


        $("#formElectricianUser").removeClass('was-validated');
        $('#formElectricianUser').trigger("reset");
        $('#v-pills-tab.nav a:first').tab('show');
        $("#user_status").select2("val", "1");
        $("#user_country_id").select2("val", "1");
        $("#user_state_id").empty().trigger('change');
        $("#user_city_id").empty().trigger('change');
        if (viewMode == 1 || isSalePerson == 1) {
            $("#formElectricianUser input:not([type=hidden]").prop('disabled', true);
            $('#formElectricianUser select').select2("enable", false);
        }
    }

    $("#call_schedule_time").select2({
        dropdownParent: $("#modalCall .modal-content")
    });

    $('#call_reminder_date_time').append(new Option('15 Min Before', '1', false, false)).trigger('change');
    $("#call_reminder_date_time").val('1');
    $('#call_reminder_date_time').trigger('change');
    $("#call_reminder_date_time").select2({
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

    $("#meeting_time").select2({
        dropdownParent: $("#modalMeeting .modal-content")
    });

    $('#meeting_reminder_date_time').append(new Option('15 Min Before', '1', false, false)).trigger('change');
    $("#meeting_reminder_date_time").val('1');
    $('#meeting_reminder_date_time').trigger('change');
    $("#meeting_reminder_date_time").select2({
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

    $("#task_due_time").select2({
        dropdownParent: $("#modalTask .modal-content")
    });

    $('#task_reminder_date_time').append(new Option('15 Min Before', '1', false, false)).trigger('change');
    $("#task_reminder_date_time").val('1');
    $('#task_reminder_date_time').trigger('change');
    $("#task_reminder_date_time").select2({
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

    function UserStatusChange(id, is_active) {
        $.ajax({
            type: 'POST',
            url: ajaxURLUserStatusChange,
            data: {
                'id': id,
                'status': is_active,
                '_token': $("[name=_token]").val()
            },
            success: function(responseText) {
                if (responseText['status'] == 1) {
                    toastr["success"](responseText['msg']);
                    getDataDetail(responseText['user_id']);
                } else {
                    toastr["error"](responseText['msg']);
                }
            }
        })
    }
</script>
