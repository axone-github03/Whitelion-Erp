<script type="text/javascript">
    var ajaxURLSearchState = "{{ route('search.state.from.country') }}";
    var ajaxURLSearchCity = "{{ route('search.city.from.state') }}";
    var ajaxURLSearchSalePerson = "{{ route('new.architects.search.sale.person') }}";
    var ajaxURLUserGetDetail = "{{ route('new.channel.partners.detail') }}";
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

    $("#user_type").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalUser .modal-body")

    });


    $("#user_country_id").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalUser .modal-body")

    });


    $("#channel_partner_d_country_id").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalUser .modal-body")

    });


    $("#channel_partner_payment_mode").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalUser .modal-body")

    });

    $("#user_city_id").select2({
        ajax: {
            url: ajaxURLSearchCity,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "country_id": function() {
                        return $("#user_country_id").val()
                    },
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


    $("#channel_partner_d_city_id").select2({
        ajax: {
            url: ajaxURLSearchCity,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "country_id": function() {
                        return $("#channel_partner_d_country_id").val()
                    },
                    "state_id": function() {
                        return $("#channel_partner_d_state_id").val()
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


    $("#channel_partner_d_state_id").select2({
        ajax: {
            url: ajaxURLSearchState,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "country_id": function() {
                        return $("#channel_partner_d_country_id").val()
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




    $("#channel_partner_reporting_manager").select2({
        ajax: {
            url: ajaxSearchReportingManager,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "user_type": function() {
                        return $("#user_type").val()
                    },
                    "user_id": function() {
                        return $("#user_id").val()
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
                if ($("#user_type").val() == null) {
                    toastr["error"]("Please select user type first");

                }

                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for a reporting manager',
        dropdownParent: $("#modalUser .modal-body ")
    });




    $("#channel_partner_sale_persons").select2({
        ajax: {
            url: ajaxSearchSalePerson,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "user_type": function() {
                        return $("#user_type").val()
                    },
                    "user_id": function() {
                        return $("#user_id").val()
                    },
                    "channel_partner_reporting_manager": function() {
                        return $("#channel_partner_reporting_manager").val()
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
                if ($("#user_type").val() == null) {
                    toastr["error"]("Please select user type first");

                }
                if ($("#channel_partner_reporting_manager").val() == null) {
                    toastr["error"]("Please select reporting manager first");

                }



                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for a sale persons',
        dropdownParent: $("#modalUser .modal-body")
    });




    $("#user_status").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalUser .modal-body")
    });


    $(document).ready(function() {


        //

        // $("#user_type").select2({
        //     minimumResultsForSearch: Infinity,
        //     dropdownParent: $("#modalUser .modal-body")

        // });


        // if(isSalePerson==1){

        //  $("#row_channel_partner_sale_persons").hide();
        //  $("#channel_partner_sale_persons").removeAttr('required');

        //  }else{

        //  $("#row_channel_partner_sale_persons").show();
        //  $("#channel_partner_sale_persons").prop('required',true);

        //  }


        var options = {
            beforeSubmit: showRequest, // pre-submit callback
            success: showResponse // post-submit callback

            // other available options:
            //url:       url         // override for form's 'action' attribute
            //type:      type        // 'get' or 'post', override for form's 'method' attribute
            //dataType:  null        // 'xml', 'script', or 'json' (expected server response type)
            //clearForm: true        // clear all form fields after successful submit
            //resetForm: true        // reset the form after successful submit

            // $.ajax options can be used here too, for example:
            //timeout:   3000
        };

        // bind form using 'ajaxForm'
        $('#formUser').ajaxForm(options);
    });

    function showRequest(formData, jqForm, options) {

        // formData is an array; here we use $.param to convert it to a string to display it
        // but the form plugin does this for you automatically when it submits the data
        var queryString = $.param(formData);

        // jqForm is a jQuery object encapsulating the form element.  To access the
        // DOM element for the form do this:
        // var formElement = jqForm[0];

        // alert('About to submit: \n\n' + queryString);

        // here we could return false to prevent the form from being submitted;
        // returning anything other than false will allow the form submit to continue

        $("#btnSave").html("Saving...");
        $("#btnSave").prop('disabled', true);
        return true;
    }

    function reloadTable() {
        table.ajax.reload(null, false);
    }

    // post-submit callback
    function showResponse(responseText, statusText, xhr, $form) {

        $("#btnSave").html("Save");
        $("#btnSave").prop('disabled', false);


        if (responseText['status'] == 1) {
            toastr["success"](responseText['msg']);
            reloadTable();
            resetInputForm();
            $("#modalUser").modal('hide');


        } else if (responseText['status'] == 0) {

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

        // for normal html responses, the first argument to the success callback
        // is the XMLHttpRequest object's responseText property

        // if the ajaxForm method was passed an Options Object with the dataType
        // property set to 'xml' then the first argument to the success callback
        // is the XMLHttpRequest object's responseXML property

        // if the ajaxForm method was passed an Options Object with the dataType
        // property set to 'json' then the first argument to the success callback
        // is the json data object returned by the server

        // alert('status: ' + statusText + '\n\nresponseText: \n' + responseText +
        //     '\n\nThe output div should have already been updated with the responseText.');
    }


    $('#v-pills-tab.nav a').click(function() {
        if ($(this).attr('id') == "v-pills-channel-partner-detail-tab") {



            $("#btnSave ").show();
            $("#btnNext").hide();


        } else {
            $("#btnNext").show();
            $("#btnSave ").hide();
        }
    })


    $("#btnNext").click(function() {

        $("#v-pills-user-detail-tab").removeClass('active');
        $("#v-pills-channel-partner-detail-tab").addClass('active');
        $("#v-pills-user-detail").removeClass('active');
        $("#v-pills-user-detail").removeClass('show');
        $("#v-pills-channel-partner-detail").addClass('active');
        $("#v-pills-channel-partner-detail").addClass('show');
        $("#btnNext").hide();
        $("#btnSave").show();
        $('#user_country_id').select2('open');
        $('#user_country_id').select2('close');

    });


    $("#copyAddressBtn").click(function() {


        if ($("#user_city_id").val() == null) {
            toastr["error"]("Please select city first");

        } else {


            $.ajax({
                type: 'GET',
                url: ajaxCityDetail + "?city_id=" + $("#user_city_id").val(),
                success: function(resultData) {


                    $("#channel_partner_d_country_id").select2("val", "1");

                    if (typeof resultData['data']['state']['id'] !== "undefined" && resultData[
                            'data']['state']['id'] !== null && typeof resultData['data']['state'][
                            'id'
                        ] !== "undefined" && resultData['data']['state']['id']) {

                        $("#channel_partner_d_state_id").empty().trigger('change');
                        var newOption = new Option(resultData['data']['state']['text'], resultData[
                            'data']['state']['id'], false, false);
                        $('#channel_partner_d_state_id').append(newOption).trigger('change');

                    }



                    if (typeof resultData['data']['id'] !== "undefined" && resultData['data'][
                            'id'
                        ] !== null && typeof resultData['data']['id'] !== "undefined" &&
                        resultData['data']['id']) {

                        $("#channel_partner_d_city_id").empty().trigger('change');
                        var newOption = new Option(resultData['data']['text'], resultData['data'][
                            'id'
                        ], false, false);
                        $('#channel_partner_d_city_id').append(newOption).trigger('change');

                    }




                    $("#channel_partner_d_pincode").val($("#user_pincode").val());
                    $("#channel_partner_d_address_line1").val($("#user_address_line1").val());
                    $("#channel_partner_d_address_line2").val($("#user_address_line2").val());

                }
            });

        }




    });

    $("#addBtnUser").click(function() {



        resetInputForm();
        $("#modalUserLabel").html("Add Channel Partner");
        $("#user_id").val(0);
        $(".loadingcls").hide();
        $("#formUser .row").show();
        // console.log($("#user_type").val());
        setTimeout(function() {
            $("#user_type").val($("#user_type").val());
            $('#user_type').trigger('change');
            // $("#user_type").select2("val", $("#user_type").val());
            changeUserType($("#user_type").val());

            $("#channel_partner_payment_mode").val('0');
            $('#channel_partner_payment_mode').trigger('change');
            $("#div_channel_partner_pending_credit").hide();


        }, 100);



    });

    function resetInputForm() {

        $('#v-pills-tab.nav a:first').tab('show');
        $("#btnNext").show();
        $("#btnSave ").hide();


        $(".channel_partner_payment_mode2").css('display', 'none');


        $('#formUser').trigger("reset");
        $("#user_status").select2("val", "1");
        $("#user_country_id").select2("val", "1");
        $("#user_state_id").empty().trigger('change');
        $("#user_city_id").empty().trigger('change');
        $("#channel_partner_reporting_manager").empty().trigger('change');
        $("#channel_partner_sale_persons").empty().trigger('change');


        $("#channel_partner_d_country_id").select2("val", "1");
        $("#channel_partner_d_state_id").empty().trigger('change');
        $("#channel_partner_d_city_id").empty().trigger('change');
        $("#formUser").removeClass('was-validated');
        $("#channel_partner_credit_limit").prop('disabled', false);
        $("#div_user_type").show();


        $('#formUser input*,#formUser select*,#formUser button*').removeAttr('disabled');








    }

    var editModeLoading = 0;

    function editDetailArchitect(id, typeOfProcess) {

        editModeLoading = 1;
        resetInputForm();

        $("#modalUser").modal('show');
        if (typeOfProcess == 'view') {
            $("#modalUserLabel").html("View Channel Partner #" + id);
        } else {
            $("#modalUserLabel").html("Edit Channel Partner #" + id);
        }
        $("#formUser .row").hide();
        $(".loadingcls").show();
        $("#btnSave ").hide();

        $.ajax({
            type: 'GET',
            url: ajaxURLUserGetDetail + "?id=" + id,
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
                    $('#user_type').trigger('change');


                    $("#user_status").val(resultData['data']['status']);
                    $('#user_status').trigger('change');

                    if (resultData['data']['type'] == 104) {
                        $("#channel_partner_gst_number").removeAttr('required');

                    } else {
                        $("#channel_partner_gst_number").prop('required', true);
                    }




                    if (typeof resultData['data']['country']['id'] !== "undefined") {

                        $("#user_country_id").val("" + resultData['data']['country']['id'] + "");
                        $('#user_country_id').trigger('change');
                    }


                    if (typeof resultData['data']['state']['id'] !== "undefined") {
                        $("#user_state_id").empty().trigger('change');
                        var newOption = new Option(resultData['data']['state']['name'], resultData['data'][
                            'state'
                        ]['id'], false, false);
                        $('#user_state_id').append(newOption).trigger('change');
                        $("#user_state_id").val("" + resultData['data']['state']['id'] + "");
                        $('#user_state_id').trigger('change');


                    }

                    if (typeof resultData['data']['city']['id'] !== "undefined") {
                        $("#user_city_id").empty().trigger('change');
                        var newOption = new Option(resultData['data']['city']['name'], resultData['data'][
                            'city'
                        ]['id'], false, false);
                        $('#user_city_id').append(newOption).trigger('change');
                        $("#user_city_id").val("" + resultData['data']['city']['id'] + "");
                        $('#user_city_id').trigger('change');


                    }

                    $("#channel_partner_firm_name").val(resultData['data']['channel_partner']['firm_name']);



                    if (typeof resultData['data']['channel_partner']['reporting_manager']['id'] !==
                        "undefined") {



                        $("#channel_partner_reporting_manager").empty().trigger('change');
                        var newOption = new Option(resultData['data']['channel_partner'][
                            'reporting_manager'
                        ]['text'], resultData['data']['channel_partner'][
                            'reporting_manager'
                        ]['id'], false, false);
                        $('#channel_partner_reporting_manager').append(newOption).trigger('change');
                        $('#channel_partner_reporting_manager').val('' + resultData['data'][
                            'channel_partner'
                        ]['reporting_manager']['id']);
                        $('#channel_partner_reporting_manager').trigger('change');

                    }
                    // console.log(resultData['data']['channel_partner']['sale_persons']);
                    if (resultData['data']['channel_partner']['sale_persons'].length > 0) {
                        $("#channel_partner_sale_persons").empty().trigger('change');
                        var selectedSalePersons = [];

                        for (var i = 0; i < resultData['data']['channel_partner']['sale_persons']
                            .length; i++) {

                            selectedSalePersons.push('' + resultData['data']['channel_partner'][
                                'sale_persons'
                            ][i]['id'] + '');

                            var newOption = new Option(resultData['data']['channel_partner']['sale_persons']
                                [i]['text'], resultData['data']['channel_partner']['sale_persons'][i][
                                    'id'
                                ], false, false);
                            $('#channel_partner_sale_persons').append(newOption).trigger('change');


                        }
                        $("#channel_partner_sale_persons").val(selectedSalePersons).change();

                    }

                    setTimeout(function() {

                        $("#channel_partner_payment_mode").val('' + resultData['data'][
                            'channel_partner'
                        ]['payment_mode'] + '');
                        $('#channel_partner_payment_mode').trigger('change');
                    }, 100);
                    $("#channel_partner_credit_limit").val(resultData['data']['channel_partner'][
                        'credit_limit'
                    ]);
                    $("#channel_partner_credit_days").val(resultData['data']['channel_partner'][
                        'credit_days'
                    ]);
                    $("#channel_partner_gst_number").val(resultData['data']['channel_partner'][
                        'gst_number'
                    ]);
                    $("#channel_partner_shipping_limit").val(resultData['data']['channel_partner'][
                        'shipping_limit'
                    ]);
                    $("#channel_partner_shipping_cost").val(resultData['data']['channel_partner'][
                        'shipping_cost'
                    ]);



                    if (typeof resultData['data']['channel_partner']['d_country']['id'] !== "undefined") {

                        $("#channel_partner_d_country_id").val("" + resultData['data']['channel_partner'][
                            'd_country'
                        ]['id'] + "");
                        $('#channel_partner_d_country_id').trigger('change')



                    }




                    if (typeof resultData['data']['channel_partner']['d_state']['id'] !== "undefined") {
                        $("#channel_partner_d_state_id").empty().trigger('change');
                        var newOption = new Option(resultData['data']['channel_partner']['d_state']['name'],
                            resultData['data']['channel_partner']['d_state']['id'], false, false);
                        $('#channel_partner_d_state_id').append(newOption).trigger('change');
                        $("#channel_partner_d_state_id").val("" + resultData['data']['channel_partner'][
                            'd_state'
                        ]['id'] + "");
                        $('#channel_partner_d_state_id').trigger('change');


                    }

                    if (typeof resultData['data']['channel_partner']['d_city']['id'] !== "undefined") {
                        $("#channel_partner_d_city_id").empty().trigger('change');
                        var newOption = new Option(resultData['data']['channel_partner']['d_city']['name'],
                            resultData['data']['channel_partner']['d_city']['id'], false, false);
                        $('#channel_partner_d_city_id').append(newOption).trigger('change');
                        $("#channel_partner_d_city_id").val("" + resultData['data']['channel_partner'][
                            'd_city'
                        ]['id'] + "");
                        $('#channel_partner_d_city_id').trigger('change');


                    }

                    $("#channel_partner_d_pincode").val(resultData['data']['channel_partner']['d_pincode']);
                    $("#channel_partner_d_address_line1").val(resultData['data']['channel_partner'][
                        'd_address_line1'
                    ]);
                    $("#channel_partner_d_address_line2").val(resultData['data']['channel_partner'][
                        'd_address_line2'
                    ]);




                    $(".loadingcls").hide();
                    $("#formUser .row").show();
                    $("#div_user_type").hide();

                    changeUserType(resultData['data']['type']);

                    if (resultData['data']['channel_partner']['payment_mode'] == 2) {

                        $("#div_channel_partner_pending_credit").show();
                        $("#channel_partner_pending_credit").val(resultData['data']['channel_partner'][
                            'pending_credit'
                        ]);
                        $("#channel_partner_credit_limit").prop('disabled', true);
                    } else {
                        $("#div_channel_partner_pending_credit").hide();
                        $("#channel_partner_credit_limit").prop('disabled', true);
                    }

                    // $("#channel_partner_data_not_verified").prop("checked",false);
                    // $("#channel_partner_data_verified").prop("checked",false);
                    // $("#channel_partner_missing_data").prop("checked",false);

                    // if(resultData['data']['channel_partner']['data_verified_status']==1){
                    //     $("#channel_partner_data_verified").prop("checked",true);
                    // }else if(resultData['data']['channel_partner']['data_verified_status']==2){
                    //    $("#channel_partner_data_not_verified").prop("checked",true);
                    // }else if(resultData['data']['channel_partner']['data_verified_status']==3){
                    //    $("#channel_partner_missing_data").prop("checked",true);
                    // }


                    if (resultData['data']['channel_partner']['data_verified'] == 1) {

                        $("#channel_partner_data_verified").prop('checked', true);

                    } else {
                        $("#channel_partner_data_verified").prop('checked', false);

                    }

                    if (resultData['data']['channel_partner']['data_not_verified'] == 1) {

                        $("#channel_partner_data_not_verified").prop('checked', true);

                    } else {
                        $("#channel_partner_data_not_verified").prop('checked', false);

                    }

                    if (resultData['data']['channel_partner']['missing_data'] == 1) {

                        $("#channel_partner_missing_data").prop('checked', true);

                    } else {

                        $("#channel_partner_missing_data").prop('checked', false);

                    }

                    if (resultData['data']['channel_partner']['tele_verified'] == 1) {

                        $("#channel_partner_tele_verified").prop('checked', true);

                    } else {

                        $("#channel_partner_tele_verified").prop('checked', false);

                    }

                    if (resultData['data']['channel_partner']['tele_not_verified'] == 1) {

                        $("#channel_partner_tele_not_verified").prop('checked', true);

                    } else {

                        $("#channel_partner_tele_not_verified").prop('checked', false);

                    }




                    editModeLoading = 0;
                    if (typeOfProcess == 'view') {

                        $('#formUser input*,#formUser select*,#formUser button*').attr('disabled',
                            'disabled');
                        $("#btnSave ").hide();

                    } else {
                        $('#v-pills-tab.nav a:first').tab('show');
                    }








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

    $('#channel_partner_d_country_id').on('change', function() {

        if (editModeLoading == 0) {


            $("#channel_partner_d_state_id").empty().trigger('change');
            $("#channel_partner_d_city_id").empty().trigger('change');
        }

    });

    $('#channel_partner_d_state_id').on('change', function() {
        if (editModeLoading == 0) {
            $("#channel_partner_d_city_id").empty().trigger('change');
        }
    });



    $('#user_type').on('change', function() {

        changeUserType($(this).val());

    });


    $('#channel_partner_payment_mode').on('change', function() {


        changePaymetMode($(this).val());

    });


    function changeUserType(userType) {

        if (editModeLoading == 0) {
            $("#channel_partner_reporting_manager").empty().trigger('change');
            $("#channel_partner_sale_persons").empty().trigger('change');


        }

        if (userType == 104) {
            $("#channel_partner_gst_number").removeAttr('required');
            $("#channel_partner_gst_number_mandatary").html("");


        } else {
            $("#channel_partner_gst_number").prop('required', true);
            $("#channel_partner_gst_number_mandatary").html("*");

        }


    }

    function changePaymetMode(paymentMode) {





        if (paymentMode == 2) {
            $(".channel_partner_payment_mode2").show();
            $("#channel_partner_credit_days").attr('required', true);
            $("#channel_partner_credit_limit").attr('required', true);
        } else {
            $(".channel_partner_payment_mode2").hide();
            $("#channel_partner_credit_days").removeAttr('required')
            $("#channel_partner_credit_limit").removeAttr('required');


        }


    }


    // function debounce(callback, wait) {
    //   let timeout;
    //   return (...args) => {
    //       clearTimeout(timeout);
    //       timeout = setTimeout(function () { callback.apply(this, args); }, wait);
    //   };
    // }


    $(document).delegate('.valid-discount', 'keyup', function() {
        var max = parseInt($(this).attr('max'));
        var min = parseInt($(this).attr('min'));
        if ($(this).val() > max) {
            $(this).val(max);
        } else if ($(this).val() < min) {
            $(this).val(min);
        }
    });


    $(document).delegate('.new-discount-cls', 'change', function() {


        $("#discountSync").html(
            '<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span  >Saving...</span>');
        $.ajax({
            type: 'POST',
            url: ajaxURLChannelPartnerDiscountSave,
            data: {
                "discount_percentage": $(this).val(),
                "id": $(this).attr('id'),
                "_token": $("[name=_token").val(),
            },
            success: function(resultData) {

                $("#discountSync").html("Saved");

                DiscountTable.ajax.reload(null, false);;

            }
        });


    });


    // $(document).delegate('.new-discount-cls','keyup', debounce( ()=>{


    //



    // },500));


    $(function() {
        $(".new-discount-cls").change(function() {
            var max = parseInt($(this).attr('max'));
            var min = parseInt($(this).attr('min'));
            if ($(this).val() > max) {
                $(this).val(max);
            } else if ($(this).val() < min) {
                $(this).val(min);
            }
        });
    });


    //ajaxURLChannelPartnerDiscountSearchProductGroup
    $("#discount_product_group_id").select2({
        ajax: {
            url: ajaxURLChannelPartnerDiscountSearchProductGroup,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    "user_type": function() {
                        return $("#user_type").val()
                    },
                    "user_id": function() {
                        return $("#user_id").val()
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
                if ($("#user_type").val() == null) {
                    toastr["error"]("Please select user type first");

                }

                return {
                    results: data.results,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: false
        },
        placeholder: 'Search for a product group',
        dropdownParent: $("#modalDiscount .modal-body")
    });

    $("#cpt_discount_product_group_id").select2({
        ajax: {
            url: ajaxURLChannelPartnerDiscountSearchProductGroup,
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
        placeholder: 'Search product group',
        dropdownParent: $("#modalChannelPartenerTypeDiscount .modal-body")
    });

    $("#cpt_discount_channel_partners").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalChannelPartenerTypeDiscount .modal-body")

    });

    // $(".discount_channel_partners").select2();


    $("#saveAllDiscount").click(function() {
        $("#discountSync").html(
            '<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span  >Saving...</span>');


        $.ajax({
            type: 'POST',
            url: ajaxURLChannelPartnerDiscountSaveAll,
            data: {
                "product_group_id": function() {
                    return $("#discount_product_group_id").val()
                },
                "_token": $("[name=_token").val(),
                "user_id": function() {
                    return $("#discount_user_id").val();
                },
                "discount_percentage": function() {
                    return $("#discount_all_discount").val()
                },
            },

            success: function(resultData) {
                $("#discount_all_discount").val(0);
                $("#discountSync").html("Saved");
                DiscountTable.ajax.reload(null, false);

            }
        });

    });





    $('#channel_partner_tele_verified').on('change', function() {

        var isChecked = $(this).is(':checked');

        if (isChecked) {

            $("#channel_partner_tele_not_verified").prop("checked", false);
            //$("#architect_missing_data").prop("checked",false);

        }

    });

    $('#channel_partner_tele_not_verified').on('change', function() {

        var isChecked = $(this).is(':checked');
        if (isChecked) {

            $("#channel_partner_tele_verified").prop("checked", false);
            // $("#architect_missing_data").prop("checked",false);


        }

    });

    $('#channel_partner_data_verified').on('change', function() {

        var isChecked = $(this).is(':checked');

        if (isChecked) {

            $("#channel_partner_data_not_verified").prop("checked", false);
            //$("#architect_missing_data").prop("checked",false);

        }

    });

    $('#channel_partner_data_not_verified').on('change', function() {

        var isChecked = $(this).is(':checked');
        if (isChecked) {

            $("#channel_partner_data_verified").prop("checked", false);
            // $("#architect_missing_data").prop("checked",false);


        }

    });

    $('#channel_partner_missing_data').on('change', function() {

        var isChecked = $(this).is(':checked');
        if (isChecked) {

            // $("#architect_data_not_verified").prop("checked",false);
            //  $("#architect_data_verified").prop("checked",false);


        }

    });





    $("#saveAllCptDiscount").click(function() {
        $("#cptdiscountSync").html('<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span  >Saving...</span>');

        $.ajax({
            type: 'POST',
            url: ajaxURLChannelPartnerDiscountCptSaveAll,
            data: {
                "product_group_id": function() {
                    return $("#cpt_discount_product_group_id").val()
                },
                "_token": $("[name=_token").val(),
                "channel_partner_type_id": function() {
                    return $("#cpt_discount_channel_partners").val();
                },
                "discount_percentage": function() {
                    return $("#cpt_discount_all_discount").val();
                },
            },

            success: function(resultData) {
                $("#cpt_discount_all_discount").val(0);
                $("#cptdiscountSync").html("Saved");
                DiscountTable.ajax.reload(null, false);
                toastr["success"](resultData['msg']);
            }
        });

    });


    $('#channel_partner_tele_verified').on('change', function() {

        var isChecked = $(this).is(':checked');

        if (isChecked) {

            $("#channel_partner_tele_not_verified").prop("checked", false);
            //$("#architect_missing_data").prop("checked",false);

        }

    });

    $('#channel_partner_tele_not_verified').on('change', function() {

        var isChecked = $(this).is(':checked');
        if (isChecked) {

            $("#channel_partner_tele_verified").prop("checked", false);
            // $("#architect_missing_data").prop("checked",false);


        }

    });

    $('#channel_partner_data_verified').on('change', function() {

        var isChecked = $(this).is(':checked');

        if (isChecked) {

            $("#channel_partner_data_not_verified").prop("checked", false);
            //$("#architect_missing_data").prop("checked",false);

        }

    });

    $('#channel_partner_data_not_verified').on('change', function() {

        var isChecked = $(this).is(':checked');
        if (isChecked) {

            $("#channel_partner_data_verified").prop("checked", false);
            // $("#architect_missing_data").prop("checked",false);


        }

    });

    $('#channel_partner_missing_data').on('change', function() {

        var isChecked = $(this).is(':checked');
        if (isChecked) {

            // $("#architect_data_not_verified").prop("checked",false);
            //  $("#architect_data_verified").prop("checked",false);


        }

    });





    $('#discount_product_group_id').on('change', function() {


        $("#discount_all_discount").val(0);

        DiscountTable.ajax.reload(null, false);

    });

    $('#cpt_discount_product_group_id').on('change', function() {


        $("#cpt_discount_all_discount").val(0);

        cptDiscountTable.ajax.reload(null, false);

    });
</script>
