<script type="text/javascript">
    var ajaxURLSearchState = "{{ route('search.state.from.country') }}";
    var ajaxURLSearchCity = "{{ route('search.city.from.state') }}";
    var ajaxURLSearchSalePerson = "{{ route('new.electricians.search.sale.person') }}";
    var ajaxURLUserDetail = "{{ route('new.electricians.detail') }}";
    var ajaxPointLog = "{{ route('new.electricians.point.log') }}";
    var ajaxInquiryLog = "{{ route('new.electricians.inquiry.log') }}";
    var csrfToken = $("[name=_token").val();
    var selectedUserType = "{{ $data['type'] }}";
    var viewMode = "{{ $data['viewMode'] }}";
    var isSalePerson = "{{ $data['isSalePerson'] }}";
    var ajaxURLCheckUserPhoneNumber = "{{ route('user.phone.number.check') }}";
    var ajaxURLSearchStatus = "{{ route('new.electricians.search.status') }}";
    var ajaxURLgetgetelectriciansuser = "{{ route('get.electricians.user') }}";
    var ajaxURLSearchCityState = "{{ route('search.city.state.country') }}";


    $('#user_phone_number').on('keyup', function(e) {

        var number_length = $('#user_phone_number').val().length;
        if (number_length == 10) {
            $.ajax({
                type: 'POST',
                url: ajaxURLCheckUserPhoneNumber,
                data: {
                    '_token': $("[name=_token]").val(),
                    "user_phone_number": $('#user_phone_number').val(),
                    "user_id": $('#user_id').val(),
                    "is_number": 1
                },
                success: function(responseText) {
                    if (responseText['status'] == 1) {
                        // $('#user_email').attr('disabled', false);

                        // $('#phone_no_validation').hide();
                        // $('#phone_no_error_dialog').hide();
                        $('.disable input').attr('disabled', false);
                        $('.disable select').attr('disabled', false);
                        $('#btnSaveFinal').attr('disabled', false);
                        $('#email_id_validation').hide();
                        $('#phone_no_error_dialog').hide();
                    } else {
                        // $('#phone_no_error_dialog').show();
                        // $('#phone_no_validation').show();
                        // $('#error_text').text(responseText['msg']);
                        $('.disable input').attr('disabled', true);
                        $('.disable select').attr('disabled', true);
                        $('#btnSaveFinal').attr('disabled', true);
                        $('#phone_no_error_dialog').show();
                        $('#email_id_validation').show();
                        $('#error_text').text(responseText['msg']);
                    }
                }

            })
        } else {
            // $('#user_email').attr('disabled', true);
            // $('#phone_no_validation').hide();
            // $('#phone_no_error_dialog').hide();
            $('.disable input').attr('disabled', true);
            $('.disable select').attr('disabled', true);
            $('#btnSaveFinal').attr('disabled', true);
            $('#email_id_validation').hide();
            $('#phone_no_error_dialog').hide();
        }
    })

    $('#user_email').on('keyup', function(e) {

        // var email = $('#user_email').val();
        // if (email != '' && email != null) {
        //     if (validateEmail(email)) {
        //         $.ajax({
        //             type: 'POST',
        //             url: ajaxURLCheckUserPhoneNumber,
        //             data: {
        //                 '_token': $("[name=_token]").val(),
        //                 "user_email": $('#user_email').val(),
        //                 "user_id": $('#user_id').val(),
        //                 "is_number": 0
        //             },
        //             success: function(responseText) {
        //                 if (responseText['status'] == 1) {
        //                     $('.disable input').attr('disabled', false);
        //                     $('.disable select').attr('disabled', false);
        //                     $('#btnSaveFinal').attr('disabled', false);

        //                     $('#email_id_validation').hide();
        //                     $('#phone_no_error_dialog').hide();
        //                 } else {
        //                     $('.disable input').attr('disabled', true);
        //                     $('.disable select').attr('disabled', true);
        //                     $('#btnSaveFinal').attr('disabled', true);
        //                     $('#phone_no_error_dialog').show();
        //                     $('#email_id_validation').show();
        //                     $('#error_text').text(responseText['msg']);
        //                 }
        //             }

        //         })
        //     } else {
        //         $('.disable input').attr('disabled', true);
        //         $('.disable select').attr('disabled', true);
        //         $('#btnSaveFinal').attr('disabled', true);
        //         $('#email_id_validation').hide();
        //         $('#phone_no_error_dialog').hide();
        //     }
        // }
    })

    $("#user_city_id").select2({
        ajax: {
            url: ajaxURLSearchCityState,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "state_id": function() {
                        return $("#user_state_id").val()
                    },
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
        placeholder: 'Search for a city',
        dropdownParent: $("#modalElectricianUser .modal-body")
    });

    function validateEmail($email) {
        var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
        return emailReg.test($email);
    }

    $('#close_phone_no_error_dialog').on('click', function() {
        $('#phone_no_error_dialog').hide();
    })

    $("#user_country_id").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalElectricianUser .modal-body")

    });

    $("#user_state_id").select2({
        ajax: {
            url: ajaxURLSearchState,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "country_id": function() {
                        return $("#user_country_id").val()
                    },
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
        placeholder: 'Search for a state',
        dropdownParent: $("#modalElectricianUser .modal-body")
    });

    // $("#user_city_id").select2({
    //     ajax: {
    //         url: ajaxURLSearchCity,
    //         dataType: 'json',
    //         delay: 0,
    //         data: function(params) {
    //             return {
    //                 "state_id": function() {
    //                     return $("#user_state_id").val()
    //                 },
    //                 q: params.term, // search term
    //                 page: params.page
    //             };
    //         },
    //         processResults: function(data, params) {
    //             // parse the results into the format expected by Select2
    //             // since we are using custom formatting functions we do not need to
    //             // alter the remote JSON data, except to indicate that infinite
    //             // scrolling can be used
    //             params.page = params.page || 1;

    //             return {
    //                 results: data.results,
    //                 pagination: {
    //                     more: (params.page * 30) < data.total_count
    //                 }
    //             };
    //         },
    //         cache: false
    //     },
    //     placeholder: 'Search for a city',
    //     dropdownParent: $("#modalElectricianUser .modal-body")
    // });


    $("#user_status").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalElectricianUser .modal-body")
    }).on('change', function() {
        if ($(this).val() == 3) {
            $('#user_email').attr('readonly', false);
            $('#user_first_name').attr('readonly', false);
            $('#user_last_name').attr('readonly', false);
            $('#user_house_no').attr('readonly', false);
            $('#user_address_line1').attr('readonly', false);
            $('#user_address_line2').attr('readonly', false);
            $('#user_area').attr('readonly', false);
            $('#user_pincode').attr('readonly', false);
            $('.disable').removeClass('pe-none');
        }
    });

    $("#user_type").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalElectricianUser .modal-body")

    });




    $("#electrician_sale_person_id").select2({
        ajax: {
            url: ajaxURLSearchSalePerson,
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
        placeholder: 'Search for a sale person',
        dropdownParent: $("#modalElectricianUser .modal-body")
    });





    // $('.nav a').click(function() {
    //     if ($(this).attr('id') == "v-pills-user-extra-detail-tab") {


    //         $("#btnNext").hide();
    //         $("#btnSave").show();
    //         $('#user_country_id').select2('open');
    //         $('#user_country_id').select2('close');

    //     } else {




    //         $("#btnNext").show();
    //         $("#btnSave").hide();
    //         $('#user_country_id').select2('open');
    //         $('#user_country_id').select2('close');

    //     }
    // });

    // $("#btnNext").click(function() {

    //     $("#v-pills-user-detail-tab").removeClass('active');
    //     $("#v-pills-user-extra-detail-tab").addClass('active');
    //     $("#v-pills-user-detail").removeClass('active');
    //     $("#v-pills-user-detail").removeClass('show');
    //     $("#v-pills-electrician-detail").addClass('active');
    //     $("#v-pills-electrician-detail").addClass('show');
    //     $("#btnNext").hide();
    //     $("#btnSave").show();
    //     $('#user_country_id').select2('open');
    //     $('#user_country_id').select2('close');

    // });




    $("#addBtnElectricianUser").click(function() {

        $('#user_email').attr('disabled', true);
        $('.disable input').attr('disabled', true);
        $('.disable select').attr('disabled', true);
        $('#btnSaveFinal').attr('disabled', true);
        $('.disable').removeClass('pe-none');
        $('#phone_no_validation').hide();
        $('#phone_no_error_dialog').hide();
        resetInputForm();
        $('#phone_no_validation').hide();
        $("#modalUserLabel").html("Add Electrician");
        $("#user_id").val(0);
        $(".loadingcls").hide();
        $("#formElectricianUser .row").show();
        $('#electrician_status_div').hide();
        // console.log($("#user_type").val());
        setTimeout(function() {
            $("#user_type").val($("#user_type").val());
            $('#user_type').trigger('change');
            changeUserType($("#user_type").val());

            if (isSalePerson == 1) {

                // $("#formElectricianUser input").prop('disabled', false);
                // $('#formElectricianUser select').select2("enable");

            }



        }, 100);
        $('#user_phone_number').attr('readonly', false);
        $("#duplicate_from_div").hide();
        $("#duplicate_from").prop("required", false);


        $('#electrician_note_div').hide();

    });

    function resetInputForm() {

        $("#formElectricianUser").removeClass('was-validated');
        $('#formElectricianUser').trigger("reset");
        $('#v-pills-tab.nav a:first').tab('show');

        // $("#btnNext").show();
        // $("#btnSave ").hide();

        $("#user_status").select2("val", "1");
        $("#user_country_id").select2("val", "1");
        $("#user_state_id").empty().trigger('change');
        $("#user_city_id").empty().trigger('change');

        // if (viewMode == 1 || isSalePerson == 1) {
        //     $("#formElectricianUser input:not([type=hidden]").prop('disabled', true);
        //     $('#formElectricianUser select').select2("enable", false);
        // }




    }



    var editModeLoading = 0;

    function editView(id) {

        editModeLoading = 1;
        resetInputForm();
        $("#modalElectricianUser").modal('show');
        $("#modalUserLabel").html("Edit Electrician #" + id);
        $(".loadingcls").show();
        $("#formElectricianUser").hide();
        $('#electrician_note_div').show();

        $("#duplicate_from_div").hide();
        $("#duplicate_from").prop("required", false);
        $("#duplicate_from").empty().trigger('change');
        // $("#btnNext").show();
        // $("#btnSave").hide();

        $.ajax({
            type: 'GET',
            url: ajaxURLUserDetail + "?id=" + id,
            success: function(resultData) {
                if (resultData['status'] == 1) {



                    @if (isAdminOrCompanyAdmin() == 0)
                        $('#user_phone_number').attr('readonly', true);
                        $('#user_email').attr('readonly', true);
                    @endif

                    $("#user_id").val(resultData['data']['id']);
                    $("#user_first_name").val(resultData['data']['first_name']);
                    $("#user_last_name").val(resultData['data']['last_name']);
                    $("#user_phone_number").val(resultData['data']['phone_number']);
                    $("#user_email").val(resultData['data']['email']);
                    $("#user_ctc").val(resultData['data']['ctc']);
                    $("#user_house_no").val(resultData['data']['house_no']);
                    $("#user_address_line1").val(resultData['data']['address_line1']);
                    $("#user_address_line2").val(resultData['data']['address_line2']);
                    $("#user_area").val(resultData['data']['area']);
                    $("#user_pincode").val(resultData['data']['pincode']);
                    $("#user_type").val(resultData['data']['type']);
                    // $("#user_status").val(resultData['data']['status']);

                    var newOption = new Option(resultData['data']['status_text'], resultData['data'][
                        'status'
                    ], false, false);
                    $('#user_status').append(newOption).trigger('change');
                    $("#user_status").val("" + resultData['data']['status'] + "");
                    $('#user_status').trigger('change');

                    if (resultData['data']['type'] == 301) {
                        $("#user_email").val('');
                    }



                    // if (typeof resultData['data']['country']['id'] !== "undefined") {

                    //     $("#user_country_id").val("" + resultData['data']['country']['id'] + "");
                    //     $('#user_country_id').trigger('change');

                    // }


                    // if (typeof resultData['data']['state']['id'] !== "undefined") {

                    //     var newOption = new Option(resultData['data']['state']['name'], resultData['data'][
                    //         'state'
                    //     ]['id'], false, false);
                    //     $('#user_state_id').append(newOption).trigger('change');
                    //     $("#user_state_id").val("" + resultData['data']['state']['id'] + "");
                    //     $('#user_state_id').trigger('change');


                    // }

                    if (typeof resultData['data']['city_id'] !== "undefined") {
                        var newOption = new Option(resultData['data']['city_name'], resultData['data']['city_id'], false, false);
                        $('#user_city_id').append(newOption).trigger('change');
                        $("#user_city_id").val("" + resultData['data']['city_id'] + "");
                        $('#user_city_id').trigger('change');
                    }







                    $('#user_type').trigger('change');
                    // $('#user_status').trigger('change');


                    if (resultData['data']['electrician']['pan_card'] != "") {
                        $("#electrician_pan_card_file").html(resultData['data']['electrician']['pan_card']);
                    }


                    if (typeof resultData['data']['electrician']['sale_person'] !== "undefined" && resultData['data']['electrician']['sale_person'] !== null) {
                        var newOption = new Option(resultData['data']['electrician']['sale_person']['text'], resultData['data']['electrician']['sale_person']['id'], false, false);
                        $('#electrician_sale_person_id').append(newOption).trigger('change');
                        $("#electrician_sale_person_id").val("" + resultData['data']['electrician']['sale_person']['id'] + "");
                        $('#electrician_sale_person_id').trigger('change');
                    }

                    if (isSalePerson == 1) {



                        if (resultData['data']['type'] == 301) {
                            $("#user_type").select2("enable");
                        }

                    }


                    editModeLoading = 0;
                    $("#v-pills-user-detail-tab").trigger('click');



                    $(".loadingcls").hide();
                    $("#formElectricianUser").show();


                } else {

                    toastr["error"](resultData['msg']);

                }

            }
        });

    }

    $('#user_country_id').on('change', function() {

        if (editModeLoading == 0) {

            $("#user_state_id").empty().trigger('change');
            $("#user_city_id").empty().trigger('change');
        }

    });

    $('#user_state_id').on('change', function() {

        if (editModeLoading == 0) {
            $("#user_city_id").empty().trigger('change');
        }

    });

    $('#user_type').on('change', function() {

        changeUserType($(this).val());


    });




    function changeUserType(userType) {
        userType = userType + "";




        $('#user_country_id').select2('open');
        $('#user_country_id').select2('close');

        if (userType == 301) {



            $("#row_email").hide();
            $("#user_email").removeAttr('required');
            //$("#user_pincode").removeAttr('required');
            $("#user_address_line1").removeAttr('required');
            $("#address_validation").show();
            $("#user_pincode_mandatory").html("");
            $("#user_address_line1_mandatory").html("");
            
            
            
        } else {
            
            $("#row_email").hide();
            $("#user_email").removeAttr('required');
            //  $("#user_pincode").prop('required',true);
            $("#user_address_line1").prop('required', true);
            $("#address_validation").hide();
            //$("#user_pincode_mandatory").html("*");
            $("#user_address_line1_mandatory").html("*");

        }

        if (userType == "302") {
            if (isSalePerson == 1) {


                if ($("#user_address_line1").val() == "") {
                    $("#user_address_line1").removeAttr('disabled');
                }
                $("#divFooter").show();

            }
        } else {
            if (isSalePerson == 1) {
                $("#divFooter").hide();
                $("#user_address_line1").attr('disabled', true);
            }
        }



    }


    var electricianLogUserId = 0;

    function pointLogs(userId) {

        electricianLogUserId = userId;
        $("#modalPointLog").modal('show');
        pointLogTable.ajax.reload(null, false);

    }
    var pointLogTable = $('#pointLogTable').DataTable({
        "aoColumnDefs": [{
            "bSortable": false,
            "aTargets": [0]
        }],
        "order": [
            [0, 'desc']
        ],
        "processing": true,
        "serverSide": true,
        "pageLength": 10,
        "ajax": {
            "url": ajaxPointLog,
            "type": "POST",
            "data": {
                "_token": csrfToken,
                "user_id": function() {
                    return electricianLogUserId
                },
            }
        },
        "aoColumns": [{
            "mData": "log"
        }]
    });

    var inquiryLogType = 0;

    var inquiryLogTable = $('#InquiryTable').DataTable({
        "aoColumnDefs": [{
            "bSortable": false,
            "aTargets": []
        }],
        "order": [
            [0, 'desc']
        ],
        "processing": true,
        "serverSide": true,
        "pageLength": 10,
        "ajax": {
            "url": ajaxInquiryLog,
            "type": "POST",
            "data": {
                "_token": csrfToken,
                "user_id": function() {
                    return electricianLogUserId
                },
                "type": function() {
                    return inquiryLogType
                },
            }
        },
        "aoColumns": [{
                "mData": "id"
            },
            {
                "mData": "name"
            },
            {
                "mData": "status"
            },
            {
                "mData": "quotation_amount"
            },
            {
                "mData": "column4"
            },
            {
                "mData": "column5"
            },
        ]
    });

    function inquiryLogs(userId) {
        electricianLogUserId = userId;

        $("#modalInquiryLog").modal('show');
        inquiryLogType = 0;

        inquiryLogTable.ajax.reload();

    }

    inquiryLogTable.on('xhr', function() {

        var responseData = inquiryLogTable.ajax.json();
        $("#totalInquiry").html(responseData['overview']['total_inquiry']);
        $("#totalRunningInquiry").html(responseData['overview']['total_running']);
        $("#totalWonInquiry").html(responseData['overview']['total_won']);
        $("#totalRejectedInquiry").html(responseData['overview']['total_rejected']);
        $("#totalInquiryLogQuotationAmount").html(responseData['quotationAmount']);
        $("#modalInquiryLogLabel").html(responseData['title']);

        $(".inquiry-log-active").removeClass("inquiry-log-active");

        if (responseData['type'] == "0") {
            $("#btnInquiryLogTotal").addClass('inquiry-log-active');
        } else if (responseData['type'] == "1") {
            $("#btnInquiryLogRunning").addClass('inquiry-log-active');
        } else if (responseData['type'] == "2") {
            $("#btnInquiryLogWon").addClass('inquiry-log-active');
        } else if (responseData['type'] == "3") {
            $("#btnInquiryLogLost").addClass('inquiry-log-active');
        }
    });


    $("#btnInquiryLogTotal").click(function() {

        inquiryLogType = 0;
        inquiryLogTable.ajax.reload();

    });

    $("#btnInquiryLogRunning").click(function() {
        inquiryLogType = 1;
        inquiryLogTable.ajax.reload();

    });

    $("#btnInquiryLogWon").click(function() {
        inquiryLogType = 2;
        inquiryLogTable.ajax.reload();

    });

    $("#btnInquiryLogLost").click(function() {
        inquiryLogType = 3;
        inquiryLogTable.ajax.reload();

    });


    $("#user_status").select2({
        ajax: {
            url: ajaxURLSearchStatus,
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
        placeholder: 'Search for status',
        dropdownParent: $("#modalElectricianUser .modal-content")
    }).on('change', function(){
        var selectedOption = $(this).val();

        if(selectedOption == 0){
            $("input[value='301']").attr('checked', true);
            $("input[value='302']").removeAttr('checked');
            $('[name="user_type"]').trigger('change');
            changeUserType(301);
        }else if(selectedOption == 5){
            $("input[value='301']").attr('checked', true);
            $("input[value='302']").removeAttr('checked');
            $('[name="user_type"]').trigger('change');
            changeUserType(301);
        }else if(selectedOption == 7){
            $("input[value='301']").attr('checked', true);
            $("input[value='302']").removeAttr('checked');
            $('[name="user_type"]').trigger('change');
            changeUserType(301);
        }else if(selectedOption == 8){
            $("input[value='301']").attr('checked', true);
            $("input[value='302']").removeAttr('checked');
            $('[name="user_type"]').trigger('change');
            changeUserType(301);
        }else if(selectedOption == 1){
            $("input[value='302']").attr('checked', true);
            $("input[value='301']").removeAttr('checked');
            $('[name="user_type"]').trigger('change');
            changeUserType(302);
        }

        if (selectedOption == 5) {
            $("#duplicate_from_div").show();
            $("#duplicate_from").prop("required", true);
        } else {
            $("#duplicate_from_div").hide();
            $("#duplicate_from").prop("required", false);
        }

        if(selectedOption == 2) {
            $('#notes_required_sign').show()
            $('#electrician_note').attr('required', true);
        } else {
            $('#notes_required_sign').hide()
            $('#electrician_note').attr('required', false);
        }
    });

    function getElectricianDetails(id) {

        

        $('.disable input').attr('disabled', false);
        $('.disable select').attr('disabled', false);
        $('#btnSaveFinal').attr('disabled', false);
        $('#phone_no_validation').hide();
        $('#phone_no_error_dialog').hide();

        $("#duplicate_from").empty().trigger('change');
        resetInputForm();
        $('#electrician_status_div').show();
        $("#modalElectricianUser").modal('show');
        $("#modalUserLabel").html("Edit Electrician #" + id);
        $(".loadingcls").show();
        $("#duplicate_from_div").hide();
        $('#electrician_note_div').show();
        $("#duplicate_from").prop("required", false);
        // $("#formElectricianUser").hide();

        $.ajax({
            type: 'GET',
            url: ajaxURLUserDetail + "?id=" + id,
            success: function(resultData) {
                if (resultData['status'] == 1) {
                    $("#user_id").val(resultData['data']['id']);
                    $("#user_first_name").val(resultData['data']['first_name']);
                    $("#user_last_name").val(resultData['data']['last_name']);
                    $("#user_phone_number").val(resultData['data']['phone_number']);
                    $("#user_email").val(resultData['data']['email']);
                    $("#user_ctc").val(resultData['data']['ctc']);
                    $("#user_pincode").val(resultData['data']['pincode']);
                    $("#user_address_line1").val(resultData['data']['address_line1']);
                    $("#user_address_line2").val(resultData['data']['address_line2']);
                    $("#user_type").val(resultData['data']['type']);

                    if (resultData['data']['type'] == 301) {
                        $("input[value='301']").attr('checked', true);
                    } else if (resultData['data']['type'] == 302) {
                        $("input[value='302']").attr('checked', true);
                    }
                    $('[name="user_type"]').trigger('change');

                    // var newOption = new Option(resultData['data']['status_text'], resultData['data']['status'], false, false);
                    // $('#user_status').append(newOption).trigger('change');
                    // $("#user_status").val("" + resultData['data']['status'] + "");
                    // $('#user_status').trigger('change');

                    var newOption = new Option(resultData['data']['status_text'], resultData['data']['status_id'], false, false);
                    $('#user_status').append(newOption).trigger('change');
                    $("#user_status").val("" + resultData['data']['status_id'] + "");
                    $('#user_status').trigger('change');
                    // $("#architect_firm_name").val(resultData['data']['architect']['firm_name']);


                    if (typeof resultData['data']['city_country_id'] !== "undefined") {
                        $("#user_country_id").val("" + resultData['data']['city_country_id'] + "");
                        $('#user_country_id').trigger('change');
                    }

                    if (resultData['data']['electrician']['pan_card'] != "") {
                        $("#electrician_pan_card_file").html(resultData['data']['electrician']['pan_card']);
                    }

                    if (typeof resultData['data']['city_state_id'] !== "undefined") {
                        var newOption = new Option(resultData['data']['state_name'], resultData['data']['city_state_id'], false, false);
                        $('#user_state_id').append(newOption).trigger('change');
                        $("#user_state_id").val("" + resultData['data']['city_state_id'] + "");
                        $('#user_state_id').trigger('change');
                    }

                    if (resultData['data']['duplicate_from'] != null) {
                        var selectedData = resultData['data']['duplicate_from'];
                        
                        var newOption = new Option(selectedData['text'], selectedData['id'], false, false);
                        $('#duplicate_from').append(newOption).trigger('change');
                        $("#duplicate_from").val(selectedData['id']);
                        $('#duplicate_from').trigger('change');
                    }

                    if (typeof resultData['data']['city_id'] !== "undefined") {
                        var newOption = new Option(resultData['data']['city_name'], resultData['data']['city_id'], false, false);
                        $('#user_city_id').append(newOption).trigger('change');
                        $("#user_city_id").val("" + resultData['data']['city_id'] + "");
                        $('#user_city_id').trigger('change');
                    }


                    if (typeof resultData['data']['electrician']['sale_person'] !== "undefined") {
                        var newOption = new Option(resultData['data']['electrician']['sale_person']['text'],
                            resultData['data']['electrician']['sale_person']['id'], false, false);
                        $('#electrician_sale_person_id').append(newOption).trigger('change');
                        $("#electrician_sale_person_id").val("" + resultData['data']['electrician'][
                            'sale_person'
                        ]['id'] + "");
                        $('#electrician_sale_person_id').trigger('change');
                    }

                    editModeLoading = 0;

                    $('#flexRadioDefaultDiv1').addClass('pe-none');

                    $('.change_color .select2-selection--single').css('background-color', '#eff2f7');

                    $('#user_first_name').attr('readonly', true);
                    $('#user_last_name').attr('readonly', true);
                    $('#user_phone_number').attr('readonly', true);
                    $('#user_email').attr('readonly', true);
                    $('#user_house_no').attr('readonly', true);
                    $('#user_address_line1').attr('readonly', true);
                    $('#user_address_line2').attr('readonly', true);
                    $('#user_area').attr('readonly', true);
                    $('#user_pincode').attr('readonly', true);
                    $('.disable').addClass('pe-none');
                    $('#electrician_status_div').removeClass('pe-none');
                    $(".loadingcls").hide();
                    $("#formUser").show();
                    $('#user_type').trigger('change');
                    // $('#user_status').trigger('change');
                } else {

                    toastr["error"](resultData['msg']);

                }

            }
        });
    }


    // console.log(ajaxURLgetgetelectriciansuser);

    $("#duplicate_from").select2({
        ajax: {
            url: ajaxURLgetgetelectriciansuser,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function(data, params) {
                params.page = params.page || 1;

                // Format the data as needed (id-name-number)
                var formattedResults = data.results.map(function(user) {
                    return {
                        id: user.id,
                        text: user.id + '-' + user.first_name + '  ' + user.last_name + '-' + user
                            .phone_number,
                    };
                });

                return {
                    results: formattedResults,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for User',
        dropdownParent: $("#modalElectricianUser .modal-content")
    });

    $('#btnSaveFinal').on('click', function(){
        $('#formElectricianUser').submit();
    })
</script>
