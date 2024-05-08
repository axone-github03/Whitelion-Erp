<script>
    var ajaxURLSearchCity = "{{ route('search.city.state.country') }}";
    var ajaxURLSearchSiteStage = "{{ route('crm.lead.search.site.stage') }}";
    var ajaxURLSearchSiteType = "{{ route('crm.lead.search.site.type') }}";
    var ajaxURLSearchBHK = "{{ route('crm.lead.search.bhk') }}";
    var ajaxURLSearchWantToCover = "{{ route('crm.lead.search.want.to.cover') }}";
    var ajaxURLSearchSourceType = "{{ route('crm.lead.search.source.type') }}";
    var ajaxURLSearchSource = "{{ route('crm.lead.search.source') }}";
    var ajaxURLSearchStatus = "{{ route('crm.lead.search.status') }}";
    var ajaxURLSearchSubStatus = "{{ route('crm.lead.search.sub.status') }}";
    var ajaxURLSearchCompetitors = "{{ route('crm.lead.search.competitors') }}";
    var ajaxURLSearchAssignUser = "{{ route('crm.lead.search.assigned.user') }}";
    var ajaxURLGetEditLeadDetails = "{{ route('crm.lead.edit.detail') }}";
    var ajaxURLCheckLeadPhoneNumber = "{{ route('crm.lead.phone.number.check') }}";

    $(document).ready(function() {
        
    })

    $("#leadSameAsAboveBtn").click(function() {

        if ($("#lead_city_id").val() == null) {


            toastr["error"]("Please select city first");

        } else {

            $("#lead_meeting_house_no").val($("#lead_house_no").val());
            $("#lead_meeting_addressline1").val($("#lead_addressline1").val());
            $("#lead_meeting_addressline2").val($("#lead_addressline2").val());
            $("#lead_meeting_pincode").val($("#lead_pincode").val());
            $("#lead_meeting_area").val($("#lead_area").val());

            var lead_city_id = $('#lead_city_id').select2('data');
            $("#lead_meeting_city_id").empty().trigger('change');
            var newOption = new Option(lead_city_id[0]['text'], lead_city_id[0]['id'], false, false);
            $('#lead_meeting_city_id').append(newOption).trigger('change');

        }


    });

    $("#lead_bhk").select2({
        ajax: {
            url: ajaxURLSearchBHK,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    "site_type": function() {
                        return $('#lead_site_type').val();
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
        placeholder: 'Search for BHK',
        dropdownParent: $("#modalLead .modal-content")
    });

    $("#lead_competitor").select2({
        tags: true,
        ajax: {
            url: ajaxURLSearchCompetitors,
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
        placeholder: 'Search for competitors',
        allowClear: true,
        dropdownParent: $("#modalLead .modal-content")
    });

    $("#lead_sub_status").select2({
        ajax: {
            url: ajaxURLSearchSubStatus,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    'status': function() {
                        return $("#lead_status").val();
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
        placeholder: 'Search for sub status',
        dropdownParent: $("#modalLead .modal-content")
    });


    $("#lead_status").select2({
        ajax: {
            url: ajaxURLSearchStatus,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    'type': 0,
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
        dropdownParent: $("#modalLead .modal-content")
    });



    $("#lead_city_id").select2({
        ajax: {
            url: ajaxURLSearchCity,
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
        dropdownParent: $("#modalLead .modal-content")
    });

    $("#lead_assign_to").select2({
        ajax: {
            url: ajaxURLSearchAssignUser,
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
        placeholder: 'Search Lead Owner',
        dropdownParent: $("#modalLead .modal-content")
    });


    var source_type_arr = []
    $("#lead_source_text").hide();
    $("#lead_source_type").select2({
        ajax: {
            url: ajaxURLSearchSourceType,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    source_type: function() {
                        return source_type_arr;
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
        placeholder: 'Search for source type',
        dropdownParent: $("#modalLead .modal-content")
    }).on('change', function(e) {

        source_type_value = $(this).val();
        if (source_type_value != '' && source_type_value != null) {
            source_type_arr.push(source_type_value.split('-')[1])
        }

        $("#lead_source").empty().trigger('change');
        $("#lead_source_text").val('');
        $("#lead_source_text").removeAttr('readonly');

        if (this.value.split("-")[0] == "textrequired") {
            $("#lead_source_text").show();
            $("#div_lead_source").hide();
            $("#lead_source_text").prop('required', true);
            $("#lead_source").removeAttr('required');
        } else if (this.value.split("-")[0] == "textnotrequired") {

            $("#lead_source_text").show();
            $("#div_lead_source").hide();
            $("#lead_source_text").removeAttr('required');
            $("#lead_source").removeAttr('required');
        } else if (this.value.split("-")[0] == "fix") {
            $("#lead_source_text").show();
            $("#div_lead_source").hide();
            $("#lead_source_text").prop('readonly', true);
            $("#lead_source_text").val('-');
            $("#lead_source_text").removeAttr('required');
            $("#lead_source").removeAttr('required');
        } else {
            $("#lead_source_text").hide();
            $("#div_lead_source").show();
            $("#lead_source").prop('required', true);
            $("#lead_source_text").removeAttr('readonly');
            $("#lead_source_text").removeAttr('required');
        }
    });


    $("#lead_source").select2({
        ajax: {
            url: ajaxURLSearchSource,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    source_type: function() {
                        return $("#lead_source_type").val();
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
        dropdownParent: $("#modalLead .modal-content")
    });


    $("#lead_want_to_cover").select2({
        ajax: {
            url: ajaxURLSearchWantToCover,
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
        placeholder: 'Search for want to cover',
        dropdownParent: $("#modalLead .modal-content")
    });




    $("#lead_site_stage").select2({
        ajax: {
            url: ajaxURLSearchSiteStage,
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
        placeholder: 'Search for site stage',
        dropdownParent: $("#modalLead .modal-content")
    });

    $("#lead_site_type").select2({
        ajax: {
            url: ajaxURLSearchSiteType,
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
        placeholder: 'Search for site type',
        dropdownParent: $("#modalLead .modal-content")
    }).on('change', function() {
        $('#lead_bhk').empty().trigger('change');
    });

    $("#lead_architect").select2({
        ajax: {
            url: ajaxURLSearchSource,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    source_type: "user-202,user-201"
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
        placeholder: 'Search for architect',
        dropdownParent: $("#modalLead .modal-content")
    });

    $("#lead_electrician").select2({
        ajax: {
            url: ajaxURLSearchSource,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    source_type: "user-302,user-301"
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
        placeholder: 'Search for electrician',
        dropdownParent: $("#modalLead .modal-content")
    });

    $("#lead_channel_partner").select2({
        ajax: {
            url: ajaxURLSearchSource,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    source_type: "user-101,user-102,user-103,user-104,user-105"
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
        placeholder: 'Search for channel partner',
        dropdownParent: $("#modalLead .modal-content")
    });

    $("#addLeadBtn").click(function() {
        $('.disable input').attr('disabled', true);
        $('.disable select').attr('disabled', true);
        $('#btnSaveFinal').attr('disabled', true);
        $('#phone_no_validation').hide();
        $('#phone_no_error_dialog').hide();
        $('#div_lead_closing_date').hide();

        resetInputForm();
        $('#phone_no_validation').hide();
        $('#phone_no_validation').hide();
        $("#modalLead").modal('show');
        $("#formLead .loadingcls").hide();
        $("#lead_id").val(0);

        $("#no_of_source").val('1');
        $("#moreSourceDiv").html("");


        source_type_arr = []

        var array = [101, 102, 103, 104, 105, 202, 203, 12];
        var existsInArray = array.includes({{$data['user_type']}});
        if(existsInArray){
            var source_type_id = "{{$data['source_type_id']}}";
            var source_type = "{{$data['source_type']}}";

            var source_id = "{{$data['source_id']}}";
            var source_text = "{{$data['source_text']}}";

            var newOption = new Option(source_type, source_type_id, false, false);
            $('#lead_source_type').append(newOption).trigger('change');
            $("#lead_source_type").val("" + source_type_id + "");
            $('#lead_source_type').trigger('change');

            var newOption = new Option(source_text, source_id, false, false);
            $('#lead_source').append(newOption).trigger('change');
            $("#lead_source").val("" + source_id + "");
            $('#lead_source').trigger('change');
            $("#lead_source_text").val(source_text);
            $("#lead_source_text").attr("readonly",true);

            $('#source_type_div .select2-selection--single').addClass('pe-none');
            $('#source_type_div .select2-selection--single').css('background-color', '#eff2f7');
            
            $('#source_div .select2-selection--single').addClass('pe-none');
            $('#source_div .select2-selection--single').css('background-color', '#eff2f7');

        }
    });

    function savelead() {
        $("#formLead").trigger('submit');
    }


    $('#lead_source').on('change', function() {

        var source = $("#lead_source_type").val();
        const affectedUserTypes1 = ["user-201", "user-202"];
        const affectedUserTypes2 = ["user-301", "user-302"];
        const affectedUserTypes3 = ["user-101", "user-102", "user-103", "user-104", "user-105"];
        var isInArray1 = affectedUserTypes1.includes(source);
        var isInArray2 = affectedUserTypes2.includes(source);
        var isInArray3 = affectedUserTypes3.includes(source);
        if (isInArray1 || isInArray2 || isInArray3) {

            var souce_value = $("#lead_source").val();
            var souce_value_text = $("#lead_source option:selected").text();

            var newOption = new Option(souce_value_text, souce_value, false, false);

            if (isInArray1) {

                $('#lead_architect').append(newOption).trigger('change');
                $("#lead_architect").val("" + souce_value + "");
                $('#lead_architect').trigger('change');

            } else if (isInArray2) {

                $('#lead_electrician').append(newOption).trigger('change');
                $("#lead_electrician").val("" + souce_value + "");
                $('#lead_electrician').trigger('change');

            }  else if (isInArray3) {

                $('#lead_channel_partner').append(newOption).trigger('change');
                $("#lead_channel_partner").val("" + souce_value + "");
                $('#lead_channel_partner').trigger('change');

            }

        }


    });

    var noOfSource = $("#no_of_source").val();

    $("#addMoreSource").click(function() {
        if (noOfSource != 6) {
            $("#no_of_source").val(noOfSource);

            var sourceContent = '<div class="mb-1" id="source_box_' + noOfSource +
                '" style="padding-bottom:8px;">';
            sourceContent += '<div class="row mb-1">';
            sourceContent += '<label for="lead_source_type_' + noOfSource +
                '" class="col-sm-3 col-form-label">Source Type ' + noOfSource +
                ' <code class="highlighter-rouge">*</code></label>';
            sourceContent += '<div class="col-sm-9">';
            sourceContent += '<select class="form-control select2-ajax" id="lead_source_type_' + noOfSource +
                '" name="lead_source_type_' + noOfSource + '" required> </select>';
            sourceContent += '<div class="invalid-feedback"> Please select Source Type ' + noOfSource +
                '</div>';
            sourceContent += '</div>';
            sourceContent += '</div>';
            sourceContent += '<div class="row mb-1">';
            sourceContent += '<label for="lead_source_' + noOfSource +
                '" class="col-sm-3 col-form-label">Source ' + noOfSource +
                ' <code class="highlighter-rouge">*</code></label>';
            sourceContent += '<div class="col-sm-9">';
            sourceContent += '<div id="div_lead_source_' + noOfSource + '">';
            sourceContent += '<select class="form-control select2-ajax" id="lead_source_' + noOfSource +
                '" name="lead_source_' + noOfSource + '" required> </select>';
            sourceContent += '<div class="invalid-feedback"> Please select Source ' + noOfSource + '</div>';
            sourceContent += '</div>';
            sourceContent += '<input type="text" class="form-control" id="lead_source_text_' + noOfSource +
                '" name="lead_source_text_' + noOfSource + '" placeholder="Please enter source" value="">';
            sourceContent += '</div>';
            sourceContent += '</div>';
            sourceContent += '</div>';


            $("#moreSourceDiv").append(sourceContent);
            $("#lead_source_text_" + noOfSource).hide();
            $("#lead_source_type_" + noOfSource).select2({
                ajax: {
                    url: ajaxURLSearchSourceType,
                    dataType: 'json',
                    delay: 0,
                    data: function(params) {
                        return {
                            q: params.term, // search term
                            page: params.page,
                            source_type: function() {
                                return source_type_arr;
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
                placeholder: 'Search for source type',
                dropdownParent: $("#modalLead .modal-content")
            }).on('change', function(e) {

                source_type_value = $(this).val();
                source_type_arr.push(source_type_value.split('-')[1])

                last_index = this.id.split('_').pop();
                $("#lead_source_" + last_index).empty().trigger('change');
                $("#lead_source_text_" + last_index).val('');
                $("#lead_source_text_" + last_index).removeAttr('readonly');

                if (this.value.split("-")[0] == "textrequired") {
                    $("#lead_source_text_" + last_index).show();
                    $("#div_lead_source_" + last_index).hide();

                    $("#lead_source_text_" + last_index).prop('required', true);

                    $("#lead_source_" + last_index).removeAttr('required');

                } else if (this.value.split("-")[0] == "textnotrequired") {
                    $("#lead_source_text_" + last_index).show();
                    $("#div_lead_source_" + last_index).hide();

                    $("#lead_source_text_" + last_index).removeAttr('required');
                    $("#lead_source_" + last_index).removeAttr('required');
                } else if (this.value.split("-")[0] == "fix") {
                    $("#lead_source_text_" + last_index).show();
                    $("#div_lead_source_" + last_index).hide();

                    $("#lead_source_text_" + last_index).prop('readonly', true);
                    $("#lead_source_text_" + last_index).val('-');

                    $("#lead_source_text_" + last_index).removeAttr('required');
                    $("#lead_source_" + last_index).removeAttr('required');
                } else {
                    $("#lead_source_text_" + last_index).hide();
                    $("#div_lead_source_" + last_index).show();

                    $("#lead_source_" + last_index).prop('required', true);

                    $("#lead_source_text_" + last_index).removeAttr('required');
                }


            });

            let currentIndex = noOfSource;
            $("#lead_source_" + noOfSource).select2({
                ajax: {
                    url: ajaxURLSearchSource,
                    dataType: 'json',
                    delay: 0,
                    data: function(params) {
                        return {
                            q: params.term, // search term
                            page: params.page,
                            source_type: function() {
                                return $("#lead_source_type_" + currentIndex).val();
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
                dropdownParent: $("#modalLead .modal-content")
            });


            $('#lead_source_' + noOfSource).on('change', function() {

                var source = $("#lead_source_type_" + currentIndex).val();
                const affectedUserTypes1 = ["user-201", "user-202"];
                const affectedUserTypes2 = ["user-301", "user-302"];
                const affectedUserTypes3 = ["user-101", "user-102", "user-103", "user-104", "user-105"];
                var isInArray1 = affectedUserTypes1.includes(source);
                var isInArray2 = affectedUserTypes2.includes(source);
                var isInArray3 = affectedUserTypes3.includes(source);
                if (isInArray1 || isInArray2 || isInArray3) {

                    var souce_value = $("#lead_source_" + currentIndex).val();
                    var souce_value_text = $("#lead_source_" + currentIndex + " option:selected")
                        .text();

                    var newOption = new Option(souce_value_text, souce_value, false, false);

                    if (isInArray1) {

                        $('#lead_architect').append(newOption).trigger('change');
                        $("#lead_architect").val("" + souce_value + "");
                        $('#lead_architect').trigger('change');

                    } else if (isInArray2) {

                        $('#lead_electrician').append(newOption).trigger('change');
                        $("#lead_electrician").val("" + souce_value + "");
                        $('#lead_electrician').trigger('change');
                    }  else if (isInArray3) {

                        $('#lead_channel_partner').append(newOption).trigger('change');
                        $("#lead_channel_partner").val("" + souce_value + "");
                        $('#lead_channel_partner').trigger('change');

                    }

                }


            });

            noOfSource++;
            if (noOfSource != 1) {
                $('#removeMoreSource').show();
            }
        }
    });

    $("#removeMoreSource").click(function() {
        if (noOfSource != 1) {
            $("#no_of_source").val(noOfSource);
            noOfSource--;
            $("#source_box_" + noOfSource).remove();

            if (noOfSource == 1) {
                $('#removeMoreSource').hide();
            }
        }
    });

    function editLead(id) {
        resetInputForm();
        source_type_arr = []
        $('#phone_no_validation').hide();
        $('#phone_no_error_dialog').hide();

        $("#modalLead").modal('show');
        $("#formLead .loadingcls").hide();
        $("#lead_id").val(id);
        $.ajax({
            type: 'GET',
            url: ajaxURLGetEditLeadDetails + "?id=" + id,
            success: function(responseText) {

                if (responseText['status'] == 1) {
                    var response = responseText['data']
                    if (response['is_deal'] == 1) {
                        $('#div_lead_closing_date').show();
                    }
                    $('#lead_first_name').val(response['first_name']);
                    $('#lead_last_name').val(response['last_name']);
                    $('#lead_phone_number').val(response['phone_number']);
                    $('#lead_email').val(response['email']);
                    $('#lead_house_no').val(response['house_no']);
                    $('#lead_addressline1').val(response['addressline1']);
                    $('#lead_addressline2').val(response['addressline2']);
                    $('#lead_area').val(response['area']);
                    $('#lead_pincode').val(response['pincode']);

                    var newOption = new Option(response['city'], response['city_id'], false, false);
                    $('#lead_city_id').append(newOption).trigger('change');
                    $("#lead_city_id").val("" + response['city_id'] + "");
                    $('#lead_city_id').trigger('change');

                    var optionAssignUser = new Option(response['assign_person_name'], response[
                        'assigned_to'], false, false);
                    $('#lead_assign_to').append(optionAssignUser).trigger('change');
                    $("#lead_assign_to").val("" + response['assigned_to'] + "");
                    $('#lead_assign_to').trigger('change');

                    $('#lead_meeting_house_no').val(response['meeting_house_no']);
                    $('#lead_meeting_addressline1').val(response['meeting_addressline1']);
                    $('#lead_meeting_addressline2').val(response['meeting_addressline2']);
                    $('#lead_meeting_area').val(response['meeting_area']);
                    $('#lead_meeting_pincode').val(response['meeting_pincode']);

                    var newOption = new Option(response['meeting_city'], response['meeting_city_id'], false,
                        false);
                    $('#lead_meeting_city_id').append(newOption).trigger('change');
                    $("#lead_meeting_city_id").val("" + response['meeting_city_id'] + "");
                    $('#lead_meeting_city_id').trigger('change');

                    var newOption = new Option(response['site_stage'], response['site_stage_id'], false,
                        false);
                    $('#lead_site_stage').append(newOption).trigger('change');
                    $("#lead_site_stage").val("" + response['site_stage_id'] + "");
                    $('#lead_site_stage').trigger('change');

                    var newOption = new Option(response['site_type'], response['site_type_id'], false,
                        false);
                    $('#lead_site_type').append(newOption).trigger('change');
                    $("#lead_site_type").val("" + response['site_type_id'] + "");
                    $('#lead_site_type').trigger('change');

                    var newOption = new Option(response['bhk'], response['bhk_id'], false, false);
                    $('#lead_bhk').append(newOption).trigger('change');
                    $("#lead_bhk").val("" + response['bhk_id'] + "");
                    $('#lead_bhk').trigger('change');

                    $('#lead_sq_foot').val(response['sq_foot']);

                    if (response['want_to_cover'].length > 0) {
                        $("#lead_want_to_cover").empty().trigger('change');
                        var selectedSalePersons = [];
                        for (var i = 0; i < response['want_to_cover'].length; i++) {
                            selectedSalePersons.push('' + response['want_to_cover'][i]['id'] + '');
                            var newOption = new Option(response['want_to_cover'][i]['text'], response['want_to_cover'][i]['id'], false, false);
                            $('#lead_want_to_cover').append(newOption).trigger('change');
                        }
                        $("#lead_want_to_cover").val(selectedSalePersons).change();
                    }

                    var newOption = new Option(response['source_type'], response['source_type_id'], false, false);
                    $('#lead_source_type').append(newOption).trigger('change');
                    $("#lead_source_type").val("" + response['source_type_id'] + "");
                    source_type_arr.push(response['source_type_id']);
                    $('#lead_source_type').trigger('change');

                    var newOption = new Option(response['source'], response['source_id'], false, false);
                    $('#lead_source').append(newOption).trigger('change');
                    $("#lead_source").val("" + response['source_id'] + "");
                    $('#lead_source').trigger('change');

                    $("#lead_source_text").val(response['source_id']);

                    if(response['source_type_is_editable'] == 0){
                        $('#lead_source_type').prop('disabled', true);
                        $("#lead_source_type").parent().css({"pointer-events":"none"});
                        $("#lead_source").prop('disabled', true);
                        $("#lead_source").parent().css({"pointer-events":"none"});
                        $("#lead_source_text").prop('readonly', true);
                    }



                    $('#lead_budget').val(response['budget']);
                    $('#lead_closing_date_time input').val(response['closing_date_time']);


                    if (response['competitor'].length > 0) {
                        $("#lead_competitor").empty().trigger('change');
                        var selectedSalePersons = [];
                        for (var i = 0; i < response['competitor'].length; i++) {
                            selectedSalePersons.push('' + response['competitor'][i]['id'] + '');
                            var newOption = new Option(response['competitor'][i]['text'], response[
                                'competitor'][i]['id'], false, false);
                            $('#lead_competitor').append(newOption).trigger('change');
                        }
                        $("#lead_competitor").val(selectedSalePersons).change();
                    }


                    var newOption = new Option(response['architect']['text'], response['architect']['id'],
                        false, false);
                    $('#lead_architect').append(newOption).trigger('change');
                    $("#lead_architect").val("" + response['architect']['id'] + "");
                    $('#lead_architect').trigger('change');

                    var newOption = new Option(response['electrician']['text'], response['electrician'][
                        'id'
                    ], false, false);
                    $('#lead_electrician').append(newOption).trigger('change');
                    $("#lead_electrician").val("" + response['electrician']['id'] + "");
                    $('#lead_electrician').trigger('change');

                    var newOption = new Option(response['channel_partner']['text'], response['channel_partner'][
                        'id'
                    ], false, false);
                    $('#lead_channel_partner').append(newOption).trigger('change');
                    $("#lead_channel_partner").val("" + response['channel_partner']['id'] + "");
                    $('#lead_channel_partner').trigger('change');

                    sourceCount = 0;
                    document.getElementById('moreSourceDiv').innerHTML = "";

                    $.each(response['add_more_source'], function(index, value) {
                        i = (index + 1);



                        var sourceContent = '<div class="mb-1" id="source_box_' + i +
                            '" style="padding-bottom:8px;">';
                        sourceContent += '<div class="row mb-1">';
                        sourceContent += '<label for="lead_source_type_' + i +
                            '" class="col-sm-3 col-form-label">Source Type ' + i +
                            ' <code class="highlighter-rouge">*</code></label>';
                        sourceContent += '<div class="col-sm-9">';
                        sourceContent +=
                            '<select class="form-control select2-ajax" id="lead_source_type_' +
                            i +
                            '" name="lead_source_type_' + i + '" required> </select>';
                        sourceContent +=
                            '<div class="invalid-feedback"> Please select Source Type ' +
                            i +
                            '</div>';
                        sourceContent += '</div>';
                        sourceContent += '</div>';
                        sourceContent += '<div class="row mb-1">';
                        sourceContent += '<label for="lead_source_' + i +
                            '" class="col-sm-3 col-form-label">Source ' + i +
                            ' <code class="highlighter-rouge">*</code></label>';
                        sourceContent += '<div class="col-sm-9">';
                        sourceContent += '<div id="div_lead_source_' + i + '">';
                        sourceContent +=
                            '<select class="form-control select2-ajax" id="lead_source_' +
                            i +
                            '" name="lead_source_' + i + '" required> </select>';
                        sourceContent += '<div class="invalid-feedback"> Please select Source ' +
                            i + '</div>';
                        sourceContent += '</div>';
                        sourceContent +=
                            '<input type="text" class="form-control" id="lead_source_text_' +
                            i +
                            '" name="lead_source_text_' + i +
                            '" placeholder="Please enter source" value="">';
                        sourceContent += '</div>';
                        sourceContent += '</div>';
                        sourceContent += '</div>';


                        $("#moreSourceDiv").append(sourceContent);
                        $("#lead_source_text_" + i).hide();

                        

                        $("#lead_source_type_" + i).select2({
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
                            dropdownParent: $("#modalLead .modal-content")
                        }).on('change', function(e) {
                            last_index = this.id.split('_').pop();
                            $("#lead_source_" + last_index).empty().trigger('change');
                            $("#lead_source_text_" + last_index).val('');
                            $("#lead_source_text_" + last_index).removeAttr('readonly');

                            if (this.value.split("-")[0] == "textrequired") {
                                $("#lead_source_text_" + last_index).show();
                                $("#div_lead_source_" + last_index).hide();

                                $("#lead_source_text_" + last_index).prop('required', true);

                                $("#lead_source_" + last_index).removeAttr('required');

                            } else if (this.value.split("-")[0] == "textnotrequired") {
                                $("#lead_source_text_" + last_index).show();
                                $("#div_lead_source_" + last_index).hide();

                                $("#lead_source_text_" + last_index).removeAttr('required');
                                $("#lead_source_" + last_index).removeAttr('required');
                            } else if (this.value.split("-")[0] == "fix") {
                                $("#lead_source_text_" + last_index).show();
                                $("#div_lead_source_" + last_index).hide();

                                $("#lead_source_text_" + last_index).prop('readonly', true);
                                $("#lead_source_text_" + last_index).val('-');

                                $("#lead_source_text_" + last_index).removeAttr('required');
                                $("#lead_source_" + last_index).removeAttr('required');
                            } else {
                                $("#lead_source_text_" + last_index).hide();
                                $("#div_lead_source_" + last_index).show();

                                $("#lead_source_" + last_index).prop('required', true);

                                $("#lead_source_text_" + last_index).removeAttr('required');
                            }


                        });

                        let currentIndex = i;
                        $("#lead_source_" + i).select2({
                            ajax: {
                                url: ajaxURLSearchSource,
                                dataType: 'json',
                                delay: 0,
                                data: function(params) {
                                    return {
                                        q: params.term, // search term
                                        page: params.page,
                                        source_type: function() {
                                            return $("#lead_source_type_" +
                                                    currentIndex)
                                                .val();
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
                            dropdownParent: $("#modalLead .modal-content")
                        });


                        $('#lead_source_' + i).on('change', function() {

                            var source = $("#lead_source_type_" + currentIndex).val();
                            const affectedUserTypes1 = ["user-201", "user-202"];
                            const affectedUserTypes2 = ["user-301", "user-302"];
                            const affectedUserTypes3 = ["user-101", "user-102", "user-103", "user-104", "user-105"];
                            var isInArray1 = affectedUserTypes1.includes(source);
                            var isInArray2 = affectedUserTypes2.includes(source);
                            var isInArray3 = affectedUserTypes3.includes(source);
                            if (isInArray1 || isInArray2 || isInArray3) {

                                var souce_value = $("#lead_source_" + currentIndex).val();
                                var souce_value_text = $("#lead_source_" + currentIndex +
                                        " option:selected")
                                    .text();

                                var newOption = new Option(souce_value_text, souce_value,
                                    false,
                                    false);

                                if (isInArray1) {

                                    $('#lead_architect').append(newOption).trigger(
                                        'change');
                                    $("#lead_architect").val("" + souce_value + "");
                                    $('#lead_architect').trigger('change');

                                } else if (isInArray2) {

                                    $('#lead_electrician').append(newOption).trigger(
                                        'change');
                                    $("#lead_electrician").val("" + souce_value + "");
                                    $('#lead_electrician').trigger('change');

                                }  else if (isInArray3) {

                                    $('#lead_channel_partner').append(newOption).trigger('change');
                                    $("#lead_channel_partner").val("" + souce_value + "");
                                    $('#lead_channel_partner').trigger('change');

                                }

                            }


                        });

                        sourceCount = sourceCount + 1;
                        $("#no_of_source").val(sourceCount);
                        if (sourceCount != 0) {
                            $('#removeMoreSource').show();
                        }
                        // var newOption = new Option(response['add_more_source'][i]['text'], response['add_more_source'][i]['id'], false,false);
                        // $("#lead_source_type_" + i).append(newOption).trigger('change');
                        // $("#lead_source_type_" + i).val("" + response['add_more_source']['source_type_' + i]['contact_tag_id'] + "");
                        // $("#lead_source_type_" + i).trigger('change');

                        $("#lead_source_type_" + i).empty().trigger('change');
                        var newOption = new Option(value['type_text'], value['type_id'], false,
                            false);
                        $("#lead_source_type_" + i).append(newOption).trigger('change');
                        source_type_arr.push(value['type_id']);

                        if(value['source_type_is_editable'] == 0){
                            $("#lead_source_type_" + i).prop('disabled', true);
                            $("#lead_source_type_" + i).parent().css({"pointer-events":"none"});
                            $("#lead_source_" + i).prop('disabled', true);
                            $("#lead_source_" + i).parent().css({"pointer-events":"none"});
                            $("#lead_source_text_" + i).prop('readonly', true);
                        }
                        var selectedtype = value['type_id'];

                        if (selectedtype.split("-")[0] == "textrequired") {
                            $("#lead_source_text_" + i).show();
                            $("#lead_source_text_" + i).val(value[
                                'val_text'
                            ]);
                            $("#div_lead_source_" + i).hide();

                            $("#lead_source_text_" + i).prop('required', true);

                            $("#lead_source_" + i).removeAttr('required');

                        } else if (selectedtype.split("-")[0] == "textnotrequired") {
                            $("#lead_source_text_" + i).show();
                            $("#lead_source_text_" + i).val(value[
                                'val_text'
                            ]);
                            $("#div_lead_source_" + i).hide();

                            $("#lead_source_text_" + i).removeAttr('required');
                            $("#lead_source_" + i).removeAttr('required');
                        } else if (selectedtype.split("-")[0] == "fix") {
                            $("#lead_source_text_" + i).show();
                            $("#lead_source_text_" + i).val(value['val_text']);
                            $("#div_lead_source_" + i).hide();

                            $("#lead_source_text_" + i).prop('readonly', true);
                            $("#lead_source_text_" + i).val('-');

                            $("#lead_source_text_" + i).removeAttr('required');
                            $("#lead_source_" + i).removeAttr('required');
                        } else {
                            $("#lead_source_text_" + i).hide();
                            $("#div_lead_source_" + i).show();

                            $("#lead_source_" + i).prop('required', true);

                            $("#lead_source_text_" + i).removeAttr('required');

                            $("#lead_source_" + i).empty().trigger('change');
                            var opt_sourcevalue = new Option(value[
                                    'val_text'
                                ],
                                value['val_id'], false, false);
                            $("#lead_source_" + i).append(opt_sourcevalue).trigger('change');
                        }

                    });
                    $('.disable input').attr('disabled', false);
                    $('.disable select').attr('disabled', false);
                    $('#btnSaveFinal').attr('disabled', false);

                }
            }
        })
    }


    function resetInputForm() {
        $("#no_of_source").val(1);
        $("#formLead").removeClass('was-validated');
        $('#formLead').trigger("reset");
        $("#lead_id").val(0);
        $("#lead_city_id").empty().trigger('change');
        $("#lead_assign_to").empty().trigger('change');
        $("#lead_meeting_city_id").empty().trigger('change');
        $("#lead_electrician").empty().trigger('change');
        $("#lead_channel_partner").empty().trigger('change');
        $("#lead_architect").empty().trigger('change');
        $("#lead_competitor").empty().trigger('change');
        $("#lead_source").empty().trigger('change');
        $("#lead_source_type").empty().trigger('change');
        $("#lead_bhk").empty().trigger('change');
        $("#lead_want_to_cover").empty().trigger('change');
        $("#lead_site_stage").empty().trigger('change');
        $("#lead_site_type").empty().trigger('change');
    }

    $('#lead_phone_number').on('keyup', function(e) {

        var number_length = $('#lead_phone_number').val().length;
        if (number_length == 10) {
            $.ajax({
                type: 'POST',
                url: ajaxURLCheckLeadPhoneNumber,
                data: {
                    '_token': $("[name=_token]").val(),
                    "lead_phone_number": $('#lead_phone_number').val()
                },
                success: function(responseText) {
                    if (responseText['status'] == 1) {
                        $('.disable input').attr('disabled', false);
                        $('.disable select').attr('disabled', false);
                        $('#btnSaveFinal').attr('disabled', false);
                        $('#phone_no_validation').show().text('');
                        $('#phone_no_error_dialog').hide();
                    } else {
                        // $('#phone_no_error_dialog').show();
                        $('#phone_no_validation').show().text(responseText['msg']);
                    }
                }

            })
        } else {
            $('.disable input').attr('disabled', true);
            $('.disable select').attr('disabled', true);
            $('#btnSaveFinal').attr('disabled', true);
            $('#phone_no_validation').hide();
            $('#phone_no_error_dialog').hide();
        }
    })

    $('#close_phone_no_error_dialog').on('click', function() {
        $('#phone_no_error_dialog').hide();
    })

    function emptyArcField() {
        $("#lead_architect").empty().trigger('change');
    }
    
    function emptyEleField() {
        $("#lead_electrician").empty().trigger('change');
    }

    function emptyChenField() {
        $("#lead_channel_partner").empty().trigger('change');
    }

    $(document).ready(function() {
        $('#lead_closing_date_time input').datepicker({
            autoclose: true,
            format: 'dd-mm-yyyy',
            startDate: '-Infinity' // Allow past dates
        });
    }); 
</script>
