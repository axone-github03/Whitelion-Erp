<script type="text/javascript">
    var ajaxURLSearchState = "{{ route('search.state.from.country') }}";
    var ajaxURLSearchCity = "{{ route('search.city.from.state') }}";
    var ajaxURLSearchSalePerson = "{{ route('new.architects.search.sale.person') }}";
    var ajaxURLUserDetail = "{{ route('new.architects.detail') }}";
    var listElectriciansNonPrime = "{{ route('electricians.prime') }}";
    var listElectriciansPrime = "{{ route('electricians.prime') }}";
    var listChannelPartnerAD = "{{ route('channel.partners.ad') }}";
    var ajaxURLSearchUser = "{{ route('new.architects.search.user') }}";
    var ajaxPointLog = "{{ route('new.architects.point.log') }}";
    var ajaxInquiryLog = "{{ route('new.architects.inquiry.log') }}";
    var ajaxURLSearchSourceType = "{{ route('new.architects.search.source.type') }}";
    var ajaxURLSearchSource = "{{ route('new.architects.search.source') }}";
    var ajaxURLSearchCityState = "{{ route('search.city.state.country') }}";
    var ajaxURLSearchStatus = "{{ route('new.architects.search.status') }}";
    var ajaxURLCheckUserPhoneNumber = "{{ route('user.phone.number.check') }}";
    var ajaxURLusergetArchitect = "{{ route('get.architect.user') }}";
    var csrfToken = $("[name=_token]").val();

    var is_Edit = 0;

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
                        $('#user_email').attr('disabled', false);
                        $('#phone_no_validation').hide();
                        $('#phone_no_error_dialog').hide();
                    } else {
                        $('#phone_no_error_dialog').show();
                        $('#phone_no_validation').show();
                        $('#error_text').text(responseText['msg']);
                    }
                }

            })
        } else {
            $('#user_email').attr('disabled', true);
            $('.disable input').attr('disabled', true);
            $('.disable select').attr('disabled', true);
            $('#btnSaveFinal').attr('disabled', true);
            $('#phone_no_validation').hide();
            $('#phone_no_error_dialog').hide();
        }
    })

    $('[name="user_type"]').on('change', function() {
        if(is_Edit == 0) {
            if ($(this).val() == 202) {
                $('#user_email').attr('required');
                $('#email_validation').show();

                $('#address_validation').show();
                $('#user_house_no').attr('required');
                $('#user_address_line1').attr('required');
                $('#user_address_line2').attr('required');
                $('#user_area').attr('required');
                $('#user_pincode').attr('required');
                $('#user_city_id').attr('required');
                
                $('#architect_source_type_validation').show();
                $('#architect_source_type').attr('required');
                
                $('#architect_source_name_validation').show();
                $('#architect_source_name').attr('required');

                $('#architect_firm_name_validation').show();
                $('#architect_firm_name').attr('required');

                $('#user_email').on('keyup', function(e) {

                    var email = $('#user_email').val();
                    if (email != '' && email != null) {
                        if (validateEmail(email)) {
                            $.ajax({
                                type: 'POST',
                                url: ajaxURLCheckUserPhoneNumber,
                                data: {
                                    '_token': $("[name=_token]").val(),
                                    "user_email": $('#user_email').val(),
                                    "user_id": $('#user_id').val(),
                                    "is_number": 0
                                },
                                success: function(responseText) {
                                    if (responseText['status'] == 1) {
                                        $('.disable input').attr('disabled', false);
                                        $('.disable select').attr('disabled', false);
                                        $('#btnSaveFinal').attr('disabled', false);
                                        toastr["success"](responseText['msg']);
                                        $('#email_id_validation').hide();
                                        $('#phone_no_error_dialog').hide();
                                    } else {
                                        $('.disable input').attr('disabled', true);
                                        $('.disable select').attr('disabled', true);
                                        $('#btnSaveFinal').attr('disabled', true);
                                        $('#phone_no_error_dialog').show();
                                        $('#email_id_validation').show();
                                        $('#email_id_validation').text(responseText['msg']);
                                        $('#error_text').text(responseText['msg']);
                                    }
                                }

                            })
                        } else {
                            $('.disable input').attr('disabled', true);
                            $('.disable select').attr('disabled', true);
                            $('#btnSaveFinal').attr('disabled', true);
                            $('#email_id_validation').hide();
                            $('#phone_no_error_dialog').hide();
                        }
                    }
                })

            } else {
                $('#email_validation').hide();
                $('#user_email').removeAttr('required');
                
                $('#address_validation').hide();
                $('#user_house_no').removeAttr('required');
                $('#user_address_line1').removeAttr('required');
                $('#user_address_line2').removeAttr('required');
                $('#user_area').removeAttr('required');
                $('#user_pincode').removeAttr('required');
                $('#user_city_id').removeAttr('required');
                
                $('#architect_source_type_validation').hide();
                $('#architect_source_type').removeAttr('required');

                $('#architect_source_name_validation').hide();
                $('#architect_source_name').removeAttr('required');

                $('#architect_firm_name_validation').hide();
                $('#architect_firm_name').removeAttr('required');

                $('#user_email').on('keyup', function(e) {

                    var email_length = $('#user_email').val().length;
                    console.log(email_length);
                    if (email_length == 0) {
                        $('.disable input').attr('disabled', false);
                        $('.disable select').attr('disabled', false);
                        $('#btnSaveFinal').attr('disabled', false);
                    } else {
                        var email = $('#user_email').val();
                        if (email != '' && email != null) {
                            if (validateEmail(email)) {
                                $.ajax({
                                    type: 'POST',
                                    url: ajaxURLCheckUserPhoneNumber,
                                    data: {
                                        '_token': $("[name=_token]").val(),
                                        "user_email": $('#user_email').val(),
                                        "user_id": $('#user_id').val(),
                                        "is_number": 0
                                    },
                                    success: function(responseText) {
                                        if (responseText['status'] == 1) {
                                            $('.disable input').attr('disabled', false);
                                            $('.disable select').attr('disabled', false);
                                            $('#btnSaveFinal').attr('disabled', false);
                                            $('#email_id_validation').hide();
                                            $('#phone_no_error_dialog').hide();
                                        } else {
                                            $('.disable input').attr('disabled', true);
                                            $('.disable select').attr('disabled', true);
                                            $('#btnSaveFinal').attr('disabled', true);
                                            $('#phone_no_error_dialog').show();
                                            $('#email_id_validation').show();
                                            $('#email_id_validation').text(responseText['msg']);
                                            $('#error_text').text(responseText['msg']);
                                        }
                                    }

                                })
                            } else {
                                $('.disable input').attr('disabled', true);
                                $('.disable select').attr('disabled', true);
                                $('#btnSaveFinal').attr('disabled', true);
                                $('#email_id_validation').hide();
                                $('#phone_no_error_dialog').hide();
                            }
                        }
                    }

                })
            }
        } else {
            $('#user_email').attr('readonly', false);
            $('#user_first_name').attr('readonly', false);
            $('#user_last_name').attr('readonly', false);
            $('#user_house_no').attr('readonly', false);
            $('#user_address_line1').attr('readonly', false);
            $('#user_address_line2').attr('readonly', false);
            $('#user_area').attr('readonly', false);
            $('#user_pincode').attr('readonly', false);
            $('#architect_principal_architect_name').attr('readonly', false);
            $('#architect_firm_name').attr('readonly', false);
            $('#architect_visiting_card').attr('readonly', false);
            $('#architect_aadhar_card').attr('readonly', false);
            $('#architect_pan_card').attr('readonly', false);
            $('#architect_source_text').attr('readonly', false);

            $('#user_city_id_div').removeClass('pe-none');
            $('#architect_source_type_div').removeClass('pe-none');
            $('#architect_source_name_div').removeClass('pe-none');
            $('#architect_sale_person_id_div').removeClass('pe-none');
            $('#architect_visiting_card_input_div').removeClass('pe-none');
            $('#architect_aadhar_card_input_div').removeClass('pe-none');
            $('#architect_pan_card_input_div').removeClass('pe-none');

            $('.change_color .select2-selection--single').css('background-color', '');
        }
    })

    $(document).ready(function() {
        if(is_Edit == 0) {
            if ($('[name="user_type"]').val() == 202) {
                $('#user_email').attr('required');
                $('#email_validation').show();

                $('#address_validation').show();
                $('#user_house_no').attr('required');
                $('#user_address_line1').attr('required');
                $('#user_address_line2').attr('required');
                $('#user_area').attr('required');
                $('#user_pincode').attr('required');
                $('#user_city_id').attr('required');
                
                $('#architect_source_type_validation').show();
                $('#architect_source_type').attr('required');
                
                $('#architect_source_name_validation').show();
                $('#architect_source_name').attr('required');

                $('#architect_firm_name_validation').show();
                $('#architect_firm_name').attr('required');

                $('#user_email').on('keyup', function(e) {

                    var email = $('#user_email').val();
                    if (email != '' && email != null) {
                        if (validateEmail(email)) {
                            $.ajax({
                                type: 'POST',
                                url: ajaxURLCheckUserPhoneNumber,
                                data: {
                                    '_token': $("[name=_token]").val(),
                                    "user_email": $('#user_email').val(),
                                    "user_id": $('#user_id').val(),
                                    "is_number": 0
                                },
                                success: function(responseText) {
                                    if (responseText['status'] == 1) {
                                        $('.disable input').attr('disabled', false);
                                        $('.disable select').attr('disabled', false);
                                        $('#btnSaveFinal').attr('disabled', false);
                                        $('#email_id_validation').hide();
                                        $('#phone_no_error_dialog').hide();
                                    } else {
                                        $('.disable input').attr('disabled', true);
                                        $('.disable select').attr('disabled', true);
                                        $('#btnSaveFinal').attr('disabled', true);
                                        $('#phone_no_error_dialog').show();
                                        $('#email_id_validation').show();
                                        $('#email_id_validation').text(responseText['msg']);
                                        $('#error_text').text(responseText['msg']);
                                    }
                                }

                            })
                        } else {
                            $('.disable input').attr('disabled', true);
                            $('.disable select').attr('disabled', true);
                            $('#btnSaveFinal').attr('disabled', true);
                            $('#email_id_validation').hide();
                            $('#phone_no_error_dialog').hide();
                        }
                    }
                })

            } else {
                $('#email_validation').hide();
                $('#user_email').removeAttr('required');

                $('#address_validation').hide();
                $('#user_house_no').removeAttr('required');
                $('#user_address_line1').removeAttr('required');
                $('#user_address_line2').removeAttr('required');
                $('#user_area').removeAttr('required');
                $('#user_pincode').removeAttr('required');
                $('#user_city_id').removeAttr('required');
                
                $('#architect_source_type_validation').hide();
                $('#architect_source_type').removeAttr('required');
                
                $('#architect_source_name_validation').hide();
                $('#architect_source_name').removeAttr('required');

                $('#architect_firm_name_validation').hide();
                $('#architect_firm_name').removeAttr('required');

                $('#user_email').on('keyup', function(e) {

                    var email_length = $('#user_email').val().length;
                    if (email_length == 0) {
                        $('.disable input').attr('disabled', false);
                        $('.disable select').attr('disabled', false);
                        $('#btnSaveFinal').attr('disabled', false);
                    } else {
                        var email = $('#user_email').val();
                        if (email != '' && email != null) {
                            if (validateEmail(email)) {
                                $.ajax({
                                    type: 'POST',
                                    url: ajaxURLCheckUserPhoneNumber,
                                    data: {
                                        '_token': $("[name=_token]").val(),
                                        "user_email": $('#user_email').val(),
                                        "is_number": 0
                                    },
                                    success: function(responseText) {
                                        if (responseText['status'] == 1) {
                                            $('.disable input').attr('disabled', false);
                                            $('.disable select').attr('disabled', false);
                                            $('#btnSaveFinal').attr('disabled', false);
                                            $('#email_id_validation').hide();
                                            $('#phone_no_error_dialog').hide();
                                        } else {
                                            $('.disable input').attr('disabled', true);
                                            $('.disable select').attr('disabled', true);
                                            $('#btnSaveFinal').attr('disabled', true);
                                            $('#phone_no_error_dialog').show();
                                            $('#email_id_validation').show();
                                            $('#email_id_validation').text(responseText['msg']);
                                            $('#error_text').text(responseText['msg']);
                                        }
                                    }

                                })
                            } else {
                                $('.disable input').attr('disabled', true);
                                $('.disable select').attr('disabled', true);
                                $('#btnSaveFinal').attr('disabled', true);
                                $('#email_id_validation').hide();
                                $('#phone_no_error_dialog').hide();
                            }
                        }
                    }

                })
            }
        } else {
            $('.disable input').attr('disabled', false);
            $('.disable select').attr('disabled', false);
            $('#btnSaveFinal').attr('disabled', false);
            $('#email_id_validation').hide();
            $('#phone_no_error_dialog').hide();
        }
    })


    function validateEmail($email) {
        var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
        return emailReg.test($email);
    }

    $('#close_phone_no_error_dialog').on('click', function() {
        $('#phone_no_error_dialog').hide();
    })

    $("#user_country_id").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalUser .modal-body")

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
        dropdownParent: $("#modalUser .modal-body")
    });


    $("#user_city_id").select2({
        ajax: {
            url: ajaxURLSearchCity,
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
        dropdownParent: $("#modalUser .modal-body")
    });


    $("#user_status").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalUser .modal-body")
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
            $('#architect_principal_architect_name').attr('readonly', false);
            $('#architect_firm_name').attr('readonly', false);
            $('#architect_visiting_card').attr('readonly', false);
            $('#architect_aadhar_card').attr('readonly', false);
            $('#architect_pan_card').attr('readonly', false);
            $('#architect_source_text').attr('readonly', false);

            $('#user_city_id_div').removeClass('pe-none');
            $('#architect_source_type_div').removeClass('pe-none');
            $('#architect_source_name_div').removeClass('pe-none');
            $('#architect_sale_person_id_div').removeClass('pe-none');
            $('#architect_visiting_card_input_div').removeClass('pe-none');
            $('#architect_aadhar_card_input_div').removeClass('pe-none');
            $('#architect_pan_card_input_div').removeClass('pe-none');

            $('.change_color .select2-selection--single').css('background-color', '');
        } else {
            $('#user_email').attr('readonly', true);
            $('#user_first_name').attr('readonly', true);
            $('#user_last_name').attr('readonly', true);
            $('#user_house_no').attr('readonly', true);
            $('#user_address_line1').attr('readonly', true);
            $('#user_address_line2').attr('readonly', true);
            $('#user_area').attr('readonly', true);
            $('#user_pincode').attr('readonly', true);
            $('#architect_principal_architect_name').attr('readonly', true);
            $('#architect_firm_name').attr('readonly', true);
            $('#architect_visiting_card').attr('readonly', true);
            $('#architect_aadhar_card').attr('readonly', true);
            $('#architect_pan_card').attr('readonly', true);
            $('#architect_source_text').attr('readonly', true);

            $('#user_city_id_div').addClass('pe-none');
            $('#architect_source_type_div').addClass('pe-none');
            $('#architect_source_name_div').addClass('pe-none');
            $('#architect_sale_person_id_div').addClass('pe-none');
            $('#architect_visiting_card_input_div').addClass('pe-none');
            $('#architect_aadhar_card_input_div').addClass('pe-none');
            $('#architect_pan_card_input_div').addClass('pe-none');

            $('.change_color .select2-selection--single').css('background-color', '#eff2f7');
        }
    });;

    $("#user_type").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalUser .modal-body")

    });

    $("#architect_sale_person_id").select2({
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
        dropdownParent: $("#modalUser .modal-body")
    });

    $('#architect_birth_date').click(function() {

        $('#user_country_id').select2('open');
        $('#user_country_id').select2('close');


    });

    $('#architect_anniversary_date').click(function() {

        $('#user_country_id').select2('open');
        $('#user_country_id').select2('close');


    });

    $('.close_button').on('click', function() {
        $('#audio_url').attr('src', "");
    })

    function getArchitectsDetails(id) {


        $('.disable input').attr('disabled', false);
        $('.disable select').attr('disabled', false);
        $('#btnSaveFinal').attr('disabled', false);
        $('#phone_no_validation').hide();
        $('#phone_no_error_dialog').hide();

        $("#duplicate_from").empty().trigger('change');


        $("#modalUser").modal('show');
        $('#architect_instagram_div').show();
        $('#architect_status_div').show();
        $('#architect_recording_div').show();
        $('#architect_note_div').show();
        $("#modalUserLabel").html("Edit Architect #" + id);
        $(".loadingcls").show();
        $("#formUser").hide();
        resetInputForm();

        $("#duplicate_from_div").hide();
        $("#duplicate_from").prop("required", false);

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
                    $("#user_house_no").val(resultData['data']['house_no']);
                    $("#user_pincode").val(resultData['data']['pincode']);
                    $("#user_address_line1").val(resultData['data']['address_line1']);
                    $("#user_address_line2").val(resultData['data']['address_line2']);
                    $("#user_area").val(resultData['data']['area']);
                    $("#user_type").val(resultData['data']['type']);

                    if (resultData['data']['type'] == 201) {
                        $("input[value='201']").attr('checked', true);
                        $("input[value='202']").removeAttr('checked');
                    } else if (resultData['data']['type'] == 202) {
                        $("input[value='202']").attr('checked', true);
                        $("input[value='201']").removeAttr('checked');
                    }
                    $('[name="user_type"]').trigger('change');

                    var newOption = new Option(resultData['data']['status_text'], resultData['data']['status_id'], false, false);
                    $('#user_status').append(newOption).trigger('change');
                    $("#user_status").val("" + resultData['data']['status_id'] + "");
                    $('#user_status').trigger('change');


                    if (resultData['data']['architect']['status'] == 4) {
                        if (resultData['data']['architect']['recording'] != "") {
                            $('#audio_url').attr('src', getSpaceFilePath(resultData['data']['architect'][
                                'recording'
                            ]));
                            $('#audio_play_div').show();
                            $('#not_found_div').hide();
                        } else {
                            $('#audio_play_div').hide();
                            $('#not_found_div').show();
                        }
                        $('#audio_select_div').hide();
                    } else {

                        $('#audio_play_div').hide();
                        $('#not_found_div').hide();
                        $('#audio_select_div').show();
                    }



                    if (typeof resultData['data']['architect']['source_type_object'] !== "undefined" &&
                        resultData['data']['architect']['source_type_object'] != null) {
                        var newOption = new Option(resultData['data']['architect']['source_type_object'][
                                'text'
                            ], resultData['data']['architect']['source_type_object']['id'], false,
                            false);
                        $('#architect_source_type').append(newOption).trigger('change');
                        $("#architect_source_type").val("" + resultData['data']['architect'][
                            'source_type_object'
                        ]['id'] + "");
                        $('#architect_source_type').trigger('change');
                    }
                    if (resultData['data']['duplicate_from'] != null) {
                        var selectedData = resultData['data']['duplicate_from'];
                        
                        var newOption = new Option(selectedData['text'], selectedData['id'], false, false);
                        $('#duplicate_from').append(newOption).trigger('change');
                        $("#duplicate_from").val(selectedData['id']);
                        $('#duplicate_from').trigger('change');
                    }

                    if (typeof resultData['data']['architect']['source_type'] !== "undefined" && resultData[
                            'data']['architect']['source_type'] != null) {
                        var pieces_architect_source_type = resultData['data']['architect']['source_type']
                            .split("-");
                        if (pieces_architect_source_type[0] == "user") {
                            setTimeout(function() {
                                $("#architect_source_name").empty().trigger('change');
                                var newOption = new Option(resultData['data']['architect']['source']['text'], resultData['data']['architect']['source']['id'],false, false);
                                $('#architect_source_name').append(newOption).trigger('change');
                            }, 200);
                        } else if (pieces_architect_source_type[0] == "textrequired" || pieces_architect_source_type[0] == "textnotrequired") {
                            setTimeout(function() {
                                $("#architect_source_text").val(resultData['data']['architect'][
                                    'source_type_value'
                                ]);

                            }, 200);
                        } else if (pieces_architect_source_type[0] == "exhibition") {
                            setTimeout(function() {
                                $("#architect_source_name").empty().trigger('change');
                                var newOption = new Option(resultData['data']['architect']['source']['text'], resultData['data']['architect']['source']['id'],false, false);
                                $('#architect_source_name').append(newOption).trigger('change');
                            }, 200);
                        }
                    }


                    if (typeof resultData['data']['city']['id'] !== "undefined") {
                        var newOption = new Option(resultData['data']['city']['name'], resultData['data'][
                            'city'
                        ]['id'], false, false);
                        $('#user_city_id').append(newOption).trigger('change');
                        $("#user_city_id").val("" + resultData['data']['city']['id'] + "");
                        $('#user_city_id').trigger('change');
                    }









                    $("#architect_firm_name").val(resultData['data']['architect']['firm_name']);


                    if (resultData['data']['architect']['sale_person'] !== "undefined" && resultData['data']['architect']['sale_person'] !== null) {
                        var newOption = new Option(resultData['data']['architect']['sale_person']['text'],
                            resultData['data']['architect']['sale_person']['id'], false, false);
                        $('#architect_sale_person_id').append(newOption).trigger('change');
                        $("#architect_sale_person_id").val("" + resultData['data']['architect'][
                            'sale_person'
                        ]['id'] + "");
                        $('#architect_sale_person_id').trigger('change');
                    }


                    if (typeof resultData['data']['architect']['birth_date'] !== "undefined" && resultData[
                            'data']['architect']['birth_date'] != null) {
                        $('#architect_birth_date').datepicker('setDate', resultData['data']['architect'][
                            'birth_date'
                        ]);
                    }


                    if (typeof resultData['data']['architect']['anniversary_date'] !== "undefined" &&
                        resultData['data']['architect']['anniversary_date'] != null) {
                        $('#architect_anniversary_date').datepicker('setDate', resultData['data'][
                            'architect'
                        ]['anniversary_date']);
                    }

                    if (resultData['data']['architect']['visiting_card'] != "") {

                        $("#architect_visiting_card_file").html(resultData['data']['architect'][
                            'visiting_card'
                        ]);

                    }

                    if (resultData['data']['architect']['aadhar_card'] != "") {

                        $("#architect_aadhar_card_file").html(resultData['data']['architect'][
                            'aadhar_card'
                        ]);

                    }

                    if (resultData['data']['architect']['pan_card'] != "") {
                        $("#architect_pan_card_file").html(resultData['data']['architect']['pan_card']);
                    }

                    if (resultData['data']['architect']['instagram_link'] != "") {
                        $("#architect_instagram").val(resultData['data']['architect']['instagram_link']);
                    }

                    $("#architect_principal_architect_name").val(resultData['data']['architect'][
                        'principal_architect_name'
                    ]);



                    editModeLoading = 0;

                    $('#flexRadioDefaultDiv1').addClass('pe-none');
                    $('#flexRadioDefaultDiv2').addClass('pe-none');
                    $('#user_city_id_div').addClass('pe-none');
                    $('#architect_source_type_div').addClass('pe-none');
                    $('#architect_source_name_div').addClass('pe-none');
                    $('#architect_sale_person_id_div').addClass('pe-none');
                    // $('#architect_anniversary_date_div').addClass('pe-none');
                    // $('#architect_birth_date_div').addClass('pe-none');
                    $('#architect_visiting_card_input_div').addClass('pe-none');
                    $('#architect_aadhar_card_input_div').addClass('pe-none');
                    $('#architect_pan_card_input_div').addClass('pe-none');

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
                    $('#architect_principal_architect_name').attr('readonly', true);
                    $('#architect_firm_name').attr('readonly', true);
                    // $('#architect_birth_date').attr('readonly', true);
                    // $('#architect_anniversary_date').attr('readonly', true);
                    $('#architect_visiting_card').attr('readonly', true);
                    $('#architect_aadhar_card').attr('readonly', true);
                    $('#architect_pan_card').attr('readonly', true);
                    $('#architect_source_text').attr('readonly', true);

                    $(".loadingcls").hide();
                    $("#formUser").show();

                    $('[name="user_type"]').trigger('change');
                } else {

                    toastr["error"](resultData['msg']);

                }

            }
        });
    }


    $("#addBtnUser").click(function() {

        $('#user_email').attr('disabled', true);
        $('.disable input').attr('disabled', true);
        $('.disable select').attr('disabled', true);
        $('#btnSaveFinal').attr('disabled', true);
        $('#phone_no_validation').hide();
        $('#phone_no_error_dialog').hide();
        resetInputForm();
        $('#phone_no_validation').hide();

        $('#formUser').attr('action', '{{ route('new.architects.save') }}')

        $("#modalUserLabel").html("Add Architect");
        $("#user_id").val(0);
        $(".loadingcls").hide();
        $(".formUser").show();
        $('#architect_status_div').hide();
        $('#architect_instagram_div').hide();
        $('#architect_recording_div').hide();
        $('#architect_note_div').hide();
        $("#div_source_text").hide();
        $("#div_source_user").hide();
        $("#architect_source_type").trigger('change');
        setTimeout(function() {
            $("#user_type").val($("#user_type").val());
            $('[name="user_type"]').trigger('change');
            changeUserType($("#user_type").val());
            if (isSalePerson == 1) {
                // $("#formUser input").prop('disabled', false);
                // $('#formUser select').select2("enable");
                $("#divFooter").show();
            }
        }, 100);

        $('#flexRadioDefaultDiv1').removeClass('pe-none');
        $('#flexRadioDefaultDiv2').removeClass('pe-none');
        $('#user_city_id_div').removeClass('pe-none');
        $('#architect_source_type_div').removeClass('pe-none');
        $('#architect_source_name_div').removeClass('pe-none');
        $('#architect_sale_person_id_div').removeClass('pe-none');
        // $('#architect_anniversary_date_div').removeClass('pe-none');
        // $('#architect_birth_date_div').removeClass('pe-none');
        $('#architect_visiting_card_input_div').removeClass('pe-none');
        $('#architect_aadhar_card_input_div').removeClass('pe-none');
        $('#architect_pan_card_input_div').removeClass('pe-none');
        $('.change_color .select2-selection--single').css('background-color', '#fff');

        $('#user_first_name').attr('readonly', false);
        $('#user_last_name').attr('readonly', false);
        $('#user_phone_number').attr('readonly', false);
        $('#user_email').attr('readonly', false);
        $('#user_house_no').attr('readonly', false);
        $('#user_address_line1').attr('readonly', false);
        $('#user_address_line2').attr('readonly', false);
        $('#user_area').attr('readonly', false);
        $('#user_pincode').attr('readonly', false);
        $('#architect_principal_architect_name').attr('readonly', false);
        $('#architect_firm_name').attr('readonly', false);
        // $('#architect_birth_date').attr('readonly', false);
        // $('#architect_anniversary_date').attr('readonly', false);
        $('#architect_visiting_card').attr('readonly', false);
        $('#architect_aadhar_card').attr('readonly', false);
        $('#architect_pan_card').attr('readonly', false);
        $('#architect_source_text').attr('readonly', false);
        $("#duplicate_from_div").hide();
        $("#duplicate_from").prop("required", false);

        $('#architect_visiting_card_div').removeClass('d-none');
        $('#architect_aadhar_card_div').removeClass('d-none');
        $('#architect_pan_card_div').removeClass('d-none');
        is_Edit = 0;

    });

    function resetInputForm() {

        $("#formUser").removeClass('was-validated');
        $('#formUser').trigger("reset");
        // $("#btnNext").show();
        
        // $('#v-pills-tab.nav a:first').tab('show');
        // $("#btnNext").show();
        
        $("#architect_source_type").empty().trigger('change');
        $("#architect_source_name").empty().trigger('change');
        $("#user_status").empty().trigger('change');

        $('.nav a:first').tab('show');
        $("#user_status").select2("val", "1");
        $("#user_country_id").select2("val", "1");
        $("#user_state_id").empty().trigger('change');
        $("#user_city_id").empty().trigger('change');
        $("#architect_sale_person_id").empty().trigger('change');
        $("#architect_visiting_card_file").html("");
        $("#architect_aadhar_card_file").html("");
        $("#architect_pan_card_file").html("");

        $("#btnSaveFinal").prop('disabled', false);
        $("#btnSaveFinal").html("Save");
        
        $("#duplicate_from_div").hide();
        $("#duplicate_from").prop("required", false);

    }


    var editModeLoading = 0;

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

        $('#user_country_id').select2('open');
        $('#user_country_id').select2('close');


        changeUserType($(this).val());
        //setViewAsPerWhitelionSmartSwitchesBefore(0);

    });

    function changeUserType(userType) {

        /// MODAL SCROLL ISSUE
        // $('#architect_source_type').select2('open');
        // $('#architect_source_type').select2('close');
        // MODAL SCROLL ISSUE


        userType = userType + "";


        if (userType == "201") {

            $("#row_email").hide();
            $("#user_email").removeAttr('required');
            $(".for-prime-architect").hide();
            // $("#architect_visiting_card").removeAttr('required');
            $("#architect_aadhar_card").removeAttr('required');
            $("#architect_pan_card").removeAttr('required');
            // $("#architect_brand_using_for_switch").removeAttr('required');
            // $("#architect_brand_used_before_home_automation").removeAttr('required');
            // $("#architect_suggestion").removeAttr('required');
            $("#user_address_line1").removeAttr('required');
            $("#user_pincode").removeAttr('required');
            $("#user_pincode_mandatory").html('');
            $("#user_address_line1_mandatory").html('');
            $("#row_principal_architect").hide();
            $("#row_verified").hide();







        } else if (userType == "202") {

            $("#row_email").show();
            $("#user_email").prop('required', true);
            $("#user_pincode_mandatory").html('*');
            $("#user_address_line1_mandatory").html('*')


            $("#user_address_line1").prop('required', true);
            $("#user_pincode").prop('required', true);


            $(".for-prime-architect").show();
            if (editModeLoading == 0) {
                //$("#architect_visiting_card").prop('required', true);
                //$("#architect_aadhar_card").prop('required', true);
                $("#architect_aadhar_card").removeAttr('required');
                $("#architect_pan_card").removeAttr('required');
            } else {
                // $("#architect_visiting_card").removeAttr('required');
                $("#architect_aadhar_card").removeAttr('required');
                $("#architect_pan_card").removeAttr('required');

            }
            // $("#architect_brand_using_for_switch").prop('required', true);
            // $("#architect_brand_used_before_home_automation").prop('required', true);
            // $("#architect_suggestion").removeAttr('required');

            $("#row_principal_architect").show();
            $("#row_verified").show();

        }

        // $('#user_country_id').select2('open');
        // $('#user_country_id').select2('close');
        if (userType == "202") {
            if (isSalePerson == 1) {

                if ($("#user_pincode").val() == "") {
                    $("#user_pincode").removeAttr('disabled');

                }
                if ($("#user_email").val() == "") {
                    $("#user_email").removeAttr('disabled');

                }
                if ($("#user_address_line1").val() == "") {
                    $("#user_address_line1").removeAttr('disabled');
                }
                $("#divFooter").show();

            }
        } else {
            if (isSalePerson == 1) {
                $("#divFooter").hide();
                $("#user_pincode").attr('disabled', true);
                $("#user_email").attr('disabled', true);
                $("#user_address_line1").attr('disabled', true);
            }
        }




    }

    // $('[name=architect_whitelion_smart_switches_before]').on('change', function() {
    //     setViewAsPerWhitelionSmartSwitchesBefore(this.value);
    // });




    // function setViewAsPerWhitelionSmartSwitchesBefore(whitelion_smart_switches_before) {


    //     if (whitelion_smart_switches_before == 1) {
    //         $(".architect_whitelion_smart_switches_before_yes").show();

    //     } else {
    //         $(".architect_whitelion_smart_switches_before_yes").hide();



    //     }

    // }

    $("#architect_source_type").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalUser .modal-body")
    });





    $('#architect_tele_verified').on('change', function() {

        var isChecked = $(this).is(':checked');

        if (isChecked) {

            $("#architect_tele_not_verified").prop("checked", false);
            //$("#architect_missing_data").prop("checked",false);

        }

    });

    $('#architect_tele_not_verified').on('change', function() {

        var isChecked = $(this).is(':checked');
        if (isChecked) {

            $("#architect_tele_verified").prop("checked", false);
            // $("#architect_missing_data").prop("checked",false);


        }

    });

    $('#architect_data_verified').on('change', function() {

        var isChecked = $(this).is(':checked');

        if (isChecked) {

            $("#architect_data_not_verified").prop("checked", false);
            //$("#architect_missing_data").prop("checked",false);

        }

    });

    $('#architect_data_not_verified').on('change', function() {

        var isChecked = $(this).is(':checked');
        if (isChecked) {

            $("#architect_data_verified").prop("checked", false);
            // $("#architect_missing_data").prop("checked",false);


        }

    });

    $('#architect_missing_data').on('change', function() {

        var isChecked = $(this).is(':checked');
        if (isChecked) {

            // $("#architect_data_not_verified").prop("checked",false);
            //  $("#architect_data_verified").prop("checked",false);


        }

    });

    $('#architect_source_type').on('change', function() {
        $("#architect_source_user").empty().trigger('change');
        $("#architect_source_text").val('');
        if ($("#architect_source_type").val() != null) {

            var pieces_architect_source_type = $("#architect_source_type").val().split("-");
            $("#architect_source_text").removeAttr('required');
            $("#architect_source_user").removeAttr('required');
            $("#architect_source_text_required").html("");

            if (pieces_architect_source_type[0] == "user") {
                $("#div_source_text").hide();
                $("#div_source_user").show();
                $("#architect_source_user").prop('required', true);


                $("#sourceAddBtn").remove();

                if (pieces_architect_source_type[1] == 301) {

                    $("#div_source_user").append('<a target="_blank" href="' + listElectriciansPrime +
                        '?add=1" id="sourceAddBtn"  class="btn btn-primary">Add</a>')


                } else if (pieces_architect_source_type[1] == 302) {

                    $("#div_source_user").append('<a target="_blank" href="' + listElectriciansNonPrime +
                        '?add=1" id="sourceAddBtn"  class="btn btn-primary">Add</a>')


                } else if (pieces_architect_source_type[1] == 104) {

                    $("#div_source_user").append('<a target="_blank" href="' + listChannelPartnerAD +
                        '?add=1" id="sourceAddBtn"  class="btn btn-primary">Add</a>')



                } else {
                    $("#sourceAddBtn").remove();

                }



            } else if (pieces_architect_source_type[0] == "textrequired" || pieces_architect_source_type[0] ==
                "textnotrequired") {

                $("#div_source_text").show();
                $("#div_source_user").hide();
                if (pieces_architect_source_type[0] == "textrequired") {
                    $("#architect_source_text").prop('required', true);
                    $("#architect_source_text_required").html("*");
                } else {
                    $("#architect_source_text").removeAttr('required');
                    $("#architect_source_text_required").html("");

                }


            } else {
                $("#div_source_text").hide();
                $("#div_source_user").hide();


            }
        }


    });

    $("#architect_source_user").select2({
        ajax: {
            url: ajaxURLSearchUser,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "source_type": function() {
                        var pieces_architect_source_type = $("#architect_source_type").val().split("-");
                        return pieces_architect_source_type[1]
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
        placeholder: 'Search for a user',
        dropdownParent: $("#modalUser .modal-content")
    });

    var csrfToken = $("[name=_token").val();
    var architectLogUserId = 0;
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
                    return architectLogUserId
                },
            }
        },
        "aoColumns": [{
            "mData": "log"
        }]
    });

    function pointLogs(userId) {
        architectLogUserId = userId;
        $("#modalPointLog").modal('show');

        pointLogTable.ajax.reload(null, false);

    }

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
                    return architectLogUserId
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
        architectLogUserId = userId;
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

    $("#architect_source_type").select2({
        ajax: {
            url: ajaxURLSearchSourceType,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
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
        placeholder: 'Search for source type',
        dropdownParent: $("#modalUser .modal-content")
    }).on('change', function(e) {
        $("#architect_source_name").empty().trigger('change');
        $("#architect_source_text").val('');
        $("#architect_source_text").removeAttr('readonly');

        if (this.value.split("-")[0] == "textrequired") {
            $("#architect_source_text").show();
            $("#div_architect_source_name").hide();
            $("#architect_source_text").prop('required', true);
            $("#architect_source_name").removeAttr('required');
        } else if (this.value.split("-")[0] == "textnotrequired") {

            $("#architect_source_text").show();
            $("#div_architect_source_name").hide();
            $("#architect_source_text").removeAttr('required');
            $("#architect_source_name").removeAttr('required');
        } else if (this.value.split("-")[0] == "fix") {
            $("#architect_source_text").show();
            $("#div_architect_source_name").hide();
            $("#architect_source_text").prop('readonly', true);
            $("#architect_source_text").val('-');
            $("#architect_source_text").removeAttr('required');
            $("#architect_source_name").removeAttr('required');
        } else {
            $("#architect_source_text").hide();
            $("#div_architect_source_name").show();
            $("#architect_source_name").prop('required', true);
            $("#architect_source_text").removeAttr('readonly');
            $("#architect_source_text").removeAttr('required');
        }
    });

    $("#architect_source_name").select2({
        ajax: {
            url: ajaxURLSearchSource,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    source_type: function() {
                        return $("#architect_source_type").val();
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
        placeholder: 'Search for source',
        dropdownParent: $("#modalUser .modal-content")
    });

    $("#user_city_id").select2({
        ajax: {
            url: ajaxURLSearchCityState,
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
        placeholder: 'Search for city',
        dropdownParent: $("#modalUser .modal-content")
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
        dropdownParent: $("#modalUser .modal-content")
    });

    $("#user_status").on('change', function() {
        var selectedOption = $(this).val();

        if(selectedOption == 0){
            $("input[value='201']").attr('checked', true);
            $("input[value='202']").removeAttr('checked');
            $('[name="user_type"]').trigger('change');
            changeUserType(201);
        }else if(selectedOption == 5){
            $("input[value='201']").attr('checked', true);
            $("input[value='202']").removeAttr('checked');
            $('[name="user_type"]').trigger('change');
            changeUserType(201);
        }else if(selectedOption == 7){
            $("input[value='201']").attr('checked', true);
            $("input[value='202']").removeAttr('checked');
            $('[name="user_type"]').trigger('change');
            changeUserType(201);
        }else if(selectedOption == 8){
            $("input[value='201']").attr('checked', true);
            $("input[value='202']").removeAttr('checked');
            $('[name="user_type"]').trigger('change');
            changeUserType(201);
        }else if(selectedOption == 1){
            $("input[value='202']").attr('checked', true);
            $("input[value='201']").removeAttr('checked');
            $('[name="user_type"]').trigger('change');
            changeUserType(201);
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
            $('#architect_note').attr('required', true);
        } else {
            $('#notes_required_sign').hide()
            $('#architect_note').attr('required', false);
        }
    });

    // Trigger the change event initially if a default value is selected
    $("#user_status").trigger('change');

    $('[name="user_type"]').on('change', function() {
        if ($(this).val() == 202) {
            
            $('#user_email').attr('required');
            $('#email_validation').show();

            $('#address_validation').show();
            $('#user_house_no').attr('required');
            $('#user_address_line1').attr('required');
            $('#user_address_line2').attr('required');
            $('#user_area').attr('required');
            $('#user_pincode').attr('required');
            $('#user_city_id').attr('required');
            
            $('#architect_source_type_validation').show();
            $('#architect_source_type').attr('required');
            
            $('#architect_source_name_validation').show();
            $('#architect_source_name').attr('required');
            $('#architect_firm_name_validation').show();
            $('#architect_firm_name').attr('required');

        } else {
            $('#email_validation').hide();
            $('#user_email').removeAttr('required');

            $('#address_validation').hide();
            $('#user_house_no').removeAttr('required');
            $('#user_address_line1').removeAttr('required');
            $('#user_address_line2').removeAttr('required');
            $('#user_area').removeAttr('required');
            $('#user_pincode').removeAttr('required');
            $('#user_city_id').removeAttr('required');
            
            $('#architect_source_type_validation').hide();
            $('#architect_source_type').removeAttr('required');
            
            $('#architect_source_name_validation').hide();
            $('#architect_source_name').removeAttr('required');
            $('#architect_firm_name_validation').hide();
            $('#architect_firm_name').removeAttr('required');
        }
    })

    function editArchitect(id) {
        $("#modalUser").modal('show');
        $(".loadingcls").show();
        $("#formUser").hide();
        $("#btnSaveFinal").prop('disabled', false);
        $("#btnSaveFinal").html("Save");

        $.ajax({
            type: 'GET',
            url: ajaxURLUserDetail + "?id=" + id,
            success: function(resultData) {
                if (resultData['status'] == 1) {

                    $('#formUser').attr('action', '{{ route('new.architects.edit.save') }}')

                    $("#user_id").val(resultData['data']['id']);
                    $("#user_first_name").val(resultData['data']['first_name']);
                    $("#user_last_name").val(resultData['data']['last_name']);
                    $("#user_phone_number").val(resultData['data']['phone_number']);
                    $("#user_email").val(resultData['data']['email']);
                    $("#user_ctc").val(resultData['data']['ctc']);
                    $("#user_house_no").val(resultData['data']['house_no']);
                    $("#user_pincode").val(resultData['data']['pincode']);
                    $("#user_address_line1").val(resultData['data']['address_line1']);
                    $("#user_address_line2").val(resultData['data']['address_line2']);
                    $("#user_area").val(resultData['data']['area']);
                    $("#user_type").val(resultData['data']['type']);
                    var newOption = new Option(resultData['data']['status_text'], resultData['data'][
                        'status'
                    ], false, false);
                    $('#user_status').append(newOption).trigger('change');
                    $("#user_status").val("" + resultData['data']['status'] + "");
                    $('#user_status').trigger('change');

                    var newOption = new Option(resultData['data']['architect']['source_type_object'][
                            'text'
                        ], resultData['data']['architect']['source_type_object']['id'], false,
                        false);
                    $('#architect_source_type').append(newOption).trigger('change');
                    $("#architect_source_type").val("" + resultData['data']['architect'][
                        'source_type_object'
                    ]['id'] + "");
                    $('#architect_source_type').trigger('change');

                    var pieces_architect_source_type = resultData['data']['architect']['source_type'].split(
                        "-");
                    if (pieces_architect_source_type[0] == "user") {
                        setTimeout(function() {
                            $("#architect_source_name").empty().trigger('change');
                            var newOption = new Option(resultData['data']['architect']['source'][
                                    'text'
                                ], resultData['data']['architect']['source']['id'], false,
                                false);
                            $('#architect_source_name').append(newOption).trigger('change');
                        }, 200);
                    } else if (pieces_architect_source_type[0] == "textrequired" ||
                        pieces_architect_source_type[0] == "textnotrequired") {
                        setTimeout(function() {
                            $("#architect_source_text").val(resultData['data']['architect'][
                                'source_type_value'
                            ]);
                        }, 200);
                    }  else if (pieces_architect_source_type[0] == "exhibition") {
                        setTimeout(function() {
                            $("#architect_source_name").empty().trigger('change');
                            var newOption = new Option(resultData['data']['architect']['source']['text'], resultData['data']['architect']['source']['id'],false, false);
                            $('#architect_source_name').append(newOption).trigger('change');
                        }, 200);
                    }


                    if (typeof resultData['data']['city']['id'] !== "undefined") {
                        var newOption = new Option(resultData['data']['city']['name'], resultData['data'][
                            'city'
                        ]['id'], false, false);
                        $('#user_city_id').append(newOption).trigger('change');
                        $("#user_city_id").val("" + resultData['data']['city']['id'] + "");
                        $('#user_city_id').trigger('change');
                    }


                    $('#user_status').trigger('change');
                    
                    if (resultData['data']['type'] == 201) {
                        $("input[value='201']").attr('checked', true);
                        changeUserType(201);
                    } else if (resultData['data']['type'] == 202) {
                        $("input[value='202']").attr('checked', true);
                        changeUserType(202);
                    }
                    $('[name="user_type"]').trigger('change');

                    $("#architect_firm_name").val(resultData['data']['architect']['firm_name']);


                    if (typeof resultData['data']['architect']['sale_person'] !== "undefined") {
                        var newOption = new Option(resultData['data']['architect']['sale_person']['text'],
                            resultData['data']['architect']['sale_person']['id'], false, false);
                        $('#architect_sale_person_id').append(newOption).trigger('change');
                        $("#architect_sale_person_id").val("" + resultData['data']['architect'][
                            'sale_person'
                        ]['id'] + "");
                        $('#architect_sale_person_id').trigger('change');
                    }

                    if (typeof resultData['data']['architect']['birth_date'] !== "undefined" && resultData[
                            'data']['architect']['birth_date'] != null) {
                        $('#architect_birth_date').datepicker('setDate', resultData['data']['architect'][
                            'birth_date'
                        ]);
                    }

                    if (typeof resultData['data']['architect']['anniversary_date'] !== "undefined" &&
                        resultData['data']['architect']['anniversary_date'] != null) {
                        $('#architect_anniversary_date').datepicker('setDate', resultData['data'][
                            'architect'
                        ]['anniversary_date']);
                    }

                    $("#architect_principal_architect_name").val(resultData['data']['architect'][
                        'principal_architect_name'
                    ]);

                    @if (isAdminOrCompanyAdmin() == 0)
                        $('#user_phone_number').attr('readonly', true);
                        $('#user_email').attr('readonly', true);
                    @endif

                    $(".loadingcls").hide();
                    $("#formUser").show();
                } else {

                    toastr["error"](resultData['msg']);

                }

            }
        });
    }

    $("#duplicate_from").select2({
        ajax: {
            url: ajaxURLusergetArchitect,
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
        placeholder: 'Search for User.',
        dropdownParent: $("#modalUser .modal-content")
    });

    $('#btnSaveFinal').on('click', function(){
        // $("#btnSaveFinal").prop('disabled', true);
        // $("#btnSaveFinal").html("Saving...");
        $('#formUser').submit();
    })

    function editDetailArchitect(id) {

        is_Edit = 1;
        $('#formUser').attr('action', '{{ route('new.architects.edit.save') }}')
        $("#modalUser").modal('show');
        $(".loadingcls").show();
        $("#formUser").hide();
        $("#btnSaveFinal").prop('disabled', false);
        $("#btnSaveFinal").html("Save");

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
                    $("#user_house_no").val(resultData['data']['house_no']);
                    $("#user_pincode").val(resultData['data']['pincode']);
                    $("#user_address_line1").val(resultData['data']['address_line1']);
                    $("#user_address_line2").val(resultData['data']['address_line2']);
                    $("#user_area").val(resultData['data']['area']);

                    $('[name="user_type"]').attr('checked', false);
                    $("#user_type").val(resultData['data']['type']);

                    if(resultData['data']['type'] == 202) {
                        $('#user_email').attr('required');
                        $('#email_validation').show();
                        changeUserType(202);
                    } else {
                        $('#user_email').attr('required', false);
                        $('#email_validation').hide();
                        changeUserType(201);
                    }
                    $('[name="user_type"]').trigger('change');

                    var newOption = new Option(resultData['data']['status_text'], resultData['data'][
                        'status'
                    ], false, false);
                    $('#user_status').append(newOption).trigger('change');
                    $("#user_status").val("" + resultData['data']['status'] + "");
                    $('#user_status').trigger('change');

                    if (resultData['data']['architect']['source_type_object'] && typeof resultData['data']['architect']['source_type_object'] !== "undefined" && resultData['data']['architect']['source_type_object'] != null) {
                        var newOption = new Option(resultData['data']['architect']['source_type_object']['text'], resultData['data']['architect']['source_type_object']['id'], false, false);
                        $('#architect_source_type').append(newOption).trigger('change');
                        $("#architect_source_type").val("" + resultData['data']['architect']['source_type_object']['id'] + "");
                        $('#architect_source_type').trigger('change');
                    }

                    var pieces_architect_source_type = resultData['data']['architect']['source_type'].split("-");
                    if (pieces_architect_source_type[0] == "user") {
                        setTimeout(function() {
                            $("#architect_source_name").empty().trigger('change');
                            var newOption = new Option(resultData['data']['architect']['source']['text'], resultData['data']['architect']['source']['id'], false, false);
                            $('#architect_source_name').append(newOption).trigger('change');
                        }, 200);
                    } else if (pieces_architect_source_type[0] == "textrequired" || pieces_architect_source_type[0] == "textnotrequired") {
                        setTimeout(function() {
                            $("#architect_source_text").val(resultData['data']['architect']['source_type_value']);
                        }, 200);
                    } else if (pieces_architect_source_type[0] == "exhibition") {
                        setTimeout(function() {
                            console.log(resultData['data']['architect']['source']['text'] +'=========='+ resultData['data']['architect']['source']['id']);
                            $("#architect_source_name").empty().trigger('change');
                            var newOption = new Option(resultData['data']['architect']['source']['text'], resultData['data']['architect']['source']['id'],false, false);
                            $('#architect_source_name').append(newOption).trigger('change');
                        }, 200);
                    }


                    if (typeof resultData['data']['city']['id'] !== "undefined") {
                        var newOption = new Option(resultData['data']['city']['name'], resultData['data'][
                            'city'
                        ]['id'], false, false);
                        $('#user_city_id').append(newOption).trigger('change');
                        $("#user_city_id").val("" + resultData['data']['city']['id'] + "");
                        $('#user_city_id').trigger('change');
                    }


                    
                    $('#user_status').trigger('change');

                    if (resultData['data']['type'] == 201) {
                        $("input[value='201']").attr('checked', true);
                    } else if (resultData['data']['type'] == 202) {
                        $("input[value='202']").attr('checked', true);
                    }
                    $('[name="user_type"]').trigger('change');

                    $("#architect_firm_name").val(resultData['data']['architect']['firm_name']);


                    if (typeof resultData['data']['architect']['sale_person'] !== "undefined") {
                        var newOption = new Option(resultData['data']['architect']['sale_person']['text'],
                            resultData['data']['architect']['sale_person']['id'], false, false);
                        $('#architect_sale_person_id').append(newOption).trigger('change');
                        $("#architect_sale_person_id").val("" + resultData['data']['architect'][
                            'sale_person'
                        ]['id'] + "");
                        $('#architect_sale_person_id').trigger('change');
                    }

                    if (typeof resultData['data']['architect']['birth_date'] !== "undefined" && resultData[
                            'data']['architect']['birth_date'] != null) {
                        $('#architect_birth_date').datepicker('setDate', resultData['data']['architect'][
                            'birth_date'
                        ]);
                    }

                    if (typeof resultData['data']['architect']['anniversary_date'] !== "undefined" &&
                        resultData['data']['architect']['anniversary_date'] != null) {
                        $('#architect_anniversary_date').datepicker('setDate', resultData['data'][
                            'architect'
                        ]['anniversary_date']);
                    }

                    $("#architect_principal_architect_name").val(resultData['data']['architect'][
                        'principal_architect_name'
                    ]);

                    var user_type = "{{Auth::user()->type}}";
                   console.log(user_type);

                   if(user_type == 2) {
                        $('#user_phone_number').attr('readonly', true);
                    } else {
                        $('#user_phone_number').attr('readonly', false);
                    }


                    $('#user_email').attr('readonly', false);
                    $('#user_first_name').attr('readonly', false);
                    $('#user_last_name').attr('readonly', false);
                    $('#user_house_no').attr('readonly', false);
                    $('#user_address_line1').attr('readonly', false);
                    $('#user_address_line2').attr('readonly', false);
                    $('#user_area').attr('readonly', false);
                    $('#user_pincode').attr('readonly', false);
                    $('#architect_principal_architect_name').attr('readonly', false);
                    $('#architect_firm_name').attr('readonly', false);
                    $('#architect_visiting_card').attr('readonly', false);
                    $('#architect_aadhar_card').attr('readonly', false);
                    $('#architect_pan_card').attr('readonly', false);
                    $('#architect_source_text').attr('readonly', false);

                    $('#user_city_id_div').removeClass('pe-none');
                    $('#architect_source_type_div').removeClass('pe-none');
                    $('#architect_source_name_div').removeClass('pe-none');
                    $('#architect_sale_person_id_div').removeClass('pe-none');
                    $('#architect_visiting_card_input_div').removeClass('pe-none');
                    $('#architect_aadhar_card_input_div').removeClass('pe-none');
                    $('#architect_pan_card_input_div').removeClass('pe-none');

                    $('.change_color .select2-selection--single').css('background-color', '');

                    $('#architect_visiting_card_div').addClass('d-none');
                    $('#architect_aadhar_card_div').addClass('d-none');
                    $('#architect_pan_card_div').addClass('d-none');

                    $('.disable input').attr('disabled', false);
                    $('.disable select').attr('disabled', false);
                    $('#btnSaveFinal').attr('disabled', false);
                    $('#email_id_validation').hide();
                    $('#phone_no_error_dialog').hide();


                    $(".loadingcls").hide();
                    $("#formUser").show();
                } else {

                    toastr["error"](resultData['msg']);

                }

            }
        });
    }
</script>
