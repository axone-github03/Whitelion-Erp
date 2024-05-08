<script type="text/javascript">
    var ajaxSaveAdvanceFilterView = "{{ route('crm.lead.save.advance.filter') }}";
    var ajaxURLSearchFilterValue = "{{ route('crm.lead.search.filter.value') }}";
    var ajaxURLSearchFilterSourceTypeValue = "{{ route('crm.lead.search.source.type') }}";
    var ajaxURLSearchFilterCondition = "{{ route('crm.lead.search.filter.condition') }}";
    var ajaxURLSaveFilterViewAsDefault = "{{ route('crm.filter.view.as.default.save') }}";
    var ajaxURLViewSelectedFilter = "{{ route('crm.lead.view.selected.filter') }}";
    var ajaxURLAdvanceFilterDelete = "{{ route('crm.lead.advance.filter.delete') }}";
    var ajaxGetAdvanceFilterViewDetail = "{{ route('crm.lead.advance.filter.detail') }}";
    var ajaxSearchAdvanceFilterView = "{{ route('crm.lead.search.advance.filter.view') }}";


    $('#selectAdvanceFilterColumn_0').select2().on('change', function(e) {
        oncolumnNFunctionChange();
    });

    $("#lead_filter_source_type_value_0").select2({
        ajax: {
            url: ajaxURLSearchFilterSourceTypeValue,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    is_advance_filter: 0,
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
        placeholder: 'select type',
        dropdownParent: $("#filterdropdownmodel"),
    }).on('change', function(e) {
        oncolumnNFunctionChange(0, 0, null, null, 1);
    });

    $("#lead_filter_select_value_0").select2({
        ajax: {
            url: ajaxURLSearchFilterValue,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    is_deal: {{ $data['is_deal'] }},
                    column: function() {
                        return $("#selectAdvanceFilterColumn_0").val()
                    },
                    condtion: function() {
                        return $("#selectAdvanceFilterCondtion_0").val()
                    },
                    source_type: function() {
                        return $("#lead_filter_source_type_value_0").val()
                    },

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
        placeholder: 'select value',
        // minimumInputLength: 2,
        dropdownParent: $("#filterdropdownmodel"),
    });
    $("#lead_filter_date_picker_value_0").select2({
        ajax: {
            url: ajaxURLSearchFilterValue,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    is_deal: {{ $data['is_deal'] }},
                    column: function() {
                        return $("#selectAdvanceFilterColumn_0").val()
                    },
                    condtion: function() {
                        return $("#selectAdvanceFilterCondtion_0").val()
                    },
                    source_type: function() {
                        return $("#lead_filter_source_type_value_0").val()
                    },

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
        placeholder: 'select value',
        dropdownParent: $("#filterdropdownmodel"),
    });

    $("#lead_filter_select_value_multi_0").select2({
        ajax: {
            url: ajaxURLSearchFilterValue,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    is_deal: {{ $data['is_deal'] }},
                    column: function() {
                        return $("#selectAdvanceFilterColumn_0").val()
                    },
                    condtion: function() {
                        return $("#selectAdvanceFilterCondtion_0").val()
                    },
                    source_type: function() {
                        return $("#lead_filter_source_type_value_0").val()
                    },

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
        placeholder: 'select value',
        // minimumInputLength: 2,
        dropdownParent: $("#filterdropdownmodel"),
    });

    $("#selectAdvanceFilterCondtion_0").select2({
        ajax: {
            url: ajaxURLSearchFilterCondition,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    column: function() {
                        return $("#selectAdvanceFilterColumn_0").val()
                    },
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
        placeholder: 'select condition',
        dropdownParent: $("#filterdropdownmodel"),
    }).on('change', function(e) {
        oncolumnNFunctionChange();
    });


    var filter_count = 1;
    $("#btnAddAdvanceFilter").click(function(event) {
        event.preventDefault();
        addNLoadfilter();
    });

    $("#btnClearAdvanceFilter").click(function(event) {
        event.preventDefault();
        clearAllFilter(1);
    });

    function clearAllFilter(isfilterclear = 0) {
        var deferred = $.Deferred();
        filter_count = 1;
        $('#lead_filter_value_0').val('');
        $("#advanceFilterRows").html("");
        $('#advanceFilterInfo').text("");
        $('#lead_filter_text_field_div_0').show();
        $('#lead_filter_div_0').hide();
        $('#lead_filter_multi_div_0').hide();
        $('#lead_filter_date_picker_div_0').hide();
        $('#lead_filter_fromto_date_picker_div_0').hide();
        if (isfilterclear == 1) {
            $('#advance-filter-view').html('<div><label class="star-radio d-flex align-items-center justify-content-between"><span>Select View</span><i class="bx bxs-right-arrow"></i></label></div>');
            reloadLeadList();
        } else {
            deferred.resolve();
        }
        ischeckFilter();
        return deferred.promise();
    }

    function ischeckFilter(isfilter = 0) {
        if (filter_count > 1) {

            $('#isfiltercount').show();
            $('#isfiltercount').text(filter_count);

        } else {
            if (isfilter == 1) {
                $('#isfiltercount').show();
                $('#isfiltercount').text(filter_count);
            } else {
                $('#isfiltercount').hide();
                $('#isfiltercount').text(filter_count);
            }
        }
        $("#saveAdvanceFilter").html('Save');
    }


    $("#advanceFilterRows").on('click', '.remove', function(e) {
        e.preventDefault();
        $(this).parent().remove();
        if (filter_count == 1) {
            filter_count = 1;
            $('#advanceFilterInfo').text("");
        } else {
            filter_count--;
            $('#advanceFilterInfo').text("(no.of filter : " + (filter_count) + ")");
        }
    });

    $('#saveviewfilter').on('click', function() {
        $('#modalView').modal('show');
        $('#view_name').val('');
    })

    $('#saveFilterAsView').on('click', function() {
        $("#saveFilterAsView").html(
            '<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span  >Saving...</span>');
        let advanceFilterList = [];
        advanceFilterList.push({
            clause: $('#selectAdvanceFilterClause_0').val(),
            column: $('#selectAdvanceFilterColumn_0').val(),
            condtion: $('#selectAdvanceFilterCondtion_0').val(),
            value_text: $('#lead_filter_value_0').val(),
            value_source_type: $('#lead_filter_source_type_value_0').val(),
            value_select: $('#lead_filter_select_value_0').val(),
            value_multi_select: $('#lead_filter_select_value_multi_0').val(),
            value_date: $('#lead_filter_date_picker_value_0').val(),
            value_from_date: $('#lead_filter_from_date_picker_value_0').val(),
            value_to_date: $('#lead_filter_to_date_picker_value_0').val(),
        });

        $('#advanceFilterRows input[name="multi_filter_loop"]').each(function(ind) {
            let filtValId = $(this).attr("filt_id");

            advanceFilterList.push({
                clause: $('#selectAdvanceFilterClause_' + filtValId).val(),
                column: $('#selectAdvanceFilterColumn_' + filtValId).val(),
                condtion: $('#selectAdvanceFilterCondtion_' + filtValId).val(),
                value_text: $('#lead_filter_value_' + filtValId).val(),
                value_source_type: $('#lead_filter_source_type_value_' + filtValId).val(),
                value_select: $('#lead_filter_select_value_' + filtValId).val(),
                value_multi_select: $('#lead_filter_select_value_multi_' + filtValId).val(),
                value_date: $('#lead_filter_date_picker_value_' + filtValId).val(),
                value_from_date: $('#lead_filter_from_date_picker_value_' + filtValId).val(),
                value_to_date: $('#lead_filter_to_date_picker_value_' + filtValId).val(),
            });
        });

        var isvaluefilled = 0;
        if (advanceFilterList.length > 0) {
            $.each(advanceFilterList, function(i, eachfilter) {
                if (eachfilter['clause'] == null || eachfilter['clause'] == '') {
                    toastr["error"]("Please Selact Clause");
                    return false;
                } else if (eachfilter['column'] == null || eachfilter['column'] == '') {
                    toastr["error"]("Please Select Column");
                    return false;
                } else if (eachfilter['condtion'] == null || eachfilter['condtion'] == '') {
                    toastr["error"]("Please Select Condtion");
                    return false;
                } else {
                    var isvaluefilled = i;
                }
            });
            if (isvaluefilled = advanceFilterList.length) {
                $.ajax({
                    type: 'POST',
                    url: ajaxSaveAdvanceFilterView,
                    data: {
                        "view_name": $('#view_name').val(),
                        "is_deal": $('#is_deal_hidden').val(),
                        "is_public": $("input:radio[name=view_type]:checked").val(),
                        "arr_filter": advanceFilterList,
                        "isAdvanceFilter": isvaluefilled,
                        '_token': $("[name=_token]").val()
                    },
                    success: function(responseText) {
                        if (responseText['status'] == 1) {
                            toastr["success"](responseText['msg']);
                            $('#modalView').modal('hide');
                            filterListLoad();
                        } else {
                            toastr["error"](responseText['msg']);
                        }
                        $("#saveFilterAsView").html('Save');
                    }
                })
            }

        } else {
            $("#saveFilterAsView").html('Save');
            toastr["error"]("Please Add Filter More than 1");
        }

    })

    function oncolumnNFunctionChange(number = 0, isedit = 0, data = null, filterval = null, ischangesourcetype = 0) {


        if (isedit == 1) {

            if (number == 0) {
                
                var filter_column_list = JSON.parse("{{ json_encode(getFilterColumnCRM()) }}".replace(/&quot;/g, '"'));

                $.each(filter_column_list, function(column_key, column_val) {

                    if(data['column_id'] == column_val['id'] ){
                        $('#selectAdvanceFilterColumn_' + number).append(`<option value="${column_val['id']}" selected>${column_val['name']}</option>`).trigger('change');
                    }else{
                        $('#selectAdvanceFilterColumn_' + number).append(`<option value="${column_val['id']}">${column_val['name']}</option>`).trigger('change');
                    }
                        
                });
                $('#selectAdvanceFilterColumn_' + number).trigger('change');

            }

            $('#selectAdvanceFilterCondtion_' + number).empty().trigger('change');
            var newOption = new Option(data['condtion_text'], data['condtion_id'], false, false);
            $('#selectAdvanceFilterCondtion_' + number).append(newOption).trigger('change');
        }

        var column_id = $('#selectAdvanceFilterColumn_' + number).val();
        var condition_id = $('#selectAdvanceFilterCondtion_' + number).val();

        // clause: $('#selectAdvanceFilterClause_0').val(),
        //     column: $('#selectAdvanceFilterColumn_0').val(),
        //     condtion: $('#selectAdvanceFilterCondtion_0').val(),

        //     value_text: $('#lead_filter_value_0').val(),
        //     value_select: $('#lead_filter_select_value_0').val(),
        //     value_multi_select: $('#lead_filter_select_value_multi_0').val(),
        //     value_date: $('#lead_filter_date_picker_value_0').val(),
        //     value_from_date: $('#lead_filter_from_date_picker_value_0').val(),
        //     value_to_date: $('#lead_filter_to_date_picker_value_0').val(),

        if (condition_id != null && column_id != null) {
            var obj_filter_column = "{{ json_encode(getFilterColumnCRM()) }}".replace(/&quot;/g, '"');
            var arr_filter_column = JSON.parse(obj_filter_column)[column_id]['value_type'];
            var arr_filter_column_code = JSON.parse(obj_filter_column)[column_id]['code'];

            $('#lead_filter_select_value_' + number).empty().trigger('change');
            $('#lead_filter_select_value_multi_' + number).empty().trigger('change');
            if (ischangesourcetype == 0) {
                $('#lead_filter_source_type_value_' + number).empty().trigger('change');
                if (isedit == 1) {
                    $('#lead_filter_source_type_value_' + number).empty().trigger('change');
                    var newOption = new Option(data['source_type_text'], data['source_type_id'], false, false);
                    $('#lead_filter_source_type_value_' + number).append(newOption).trigger('change');
                }
            }

            $('#lead_filter_date_picker_value_' + number).val('');
            $('#lead_filter_from_date_picker_value_' + number).val('');
            $('#lead_filter_to_date_picker_value_' + number).val('');
            $('#lead_filter_value_' + number).val('');
            $('#lead_filter_value_' + number).removeAttr('readonly');


            if (arr_filter_column == "text") {
                $('#lead_filter_text_field_div_' + number).show();
                $('#lead_filter_div_' + number).hide();
                $('#lead_filter_multi_div_' + number).hide();
                $('#lead_filter_date_picker_div_' + number).hide();
                $('#lead_filter_fromto_date_picker_div_' + number).hide();
                $('#lead_filter_div_source_type_' + number).hide();


                if (isedit == 1) {
                    $('#lead_filter_value_' + number).val(data['value'][0]['text']);
                }

            } else if (arr_filter_column == "select") {
                var arr_filter_condition = "{{ json_encode(getFilterCondtionCRM()) }}".replace(/&quot;/g, '"');
                var arr_filter_condition = JSON.parse(arr_filter_condition)[condition_id]['value_type'];

                if (arr_filter_column_code == "leads_source") {
                    $('#lead_filter_div_source_type_' + number).show();
                } else {
                    $('#lead_filter_div_source_type_' + number).hide();
                }
                if (arr_filter_condition == "single_select") {
                    if (arr_filter_column_code == "leads_source" && ischangesourcetype == 1 && $(
                            '#lead_filter_source_type_value_' + number).val() != null) {
                        source_type = $('#lead_filter_source_type_value_' + number).val();

                        if (source_type.split("-")[0] == "textrequired") {
                            $('#lead_filter_text_field_div_' + number).show();
                            $('#lead_filter_div_' + number).hide();
                            $('#lead_filter_multi_div_' + number).hide();
                            $('#lead_filter_date_picker_div_' + number).hide();
                            $('#lead_filter_fromto_date_picker_div_' + number).hide();

                        } else if (source_type.split("-")[0] == "textnotrequired") {

                            $('#lead_filter_text_field_div_' + number).show();
                            $('#lead_filter_div_' + number).hide();
                            $('#lead_filter_multi_div_' + number).hide();
                            $('#lead_filter_date_picker_div_' + number).hide();
                            $('#lead_filter_fromto_date_picker_div_' + number).hide();

                        } else if (source_type.split("-")[0] == "fix") {
                            $('#lead_filter_text_field_div_' + number).show();
                            $('#lead_filter_div_' + number).hide();
                            $('#lead_filter_multi_div_' + number).hide();
                            $('#lead_filter_date_picker_div_' + number).hide();
                            $('#lead_filter_fromto_date_picker_div_' + number).hide();

                            $('#lead_filter_value_' + number).val('-');
                            $('#lead_filter_value_' + number).prop('readonly', true);

                        } else {
                            $('#lead_filter_div_' + number).show();
                            $('#lead_filter_text_field_div_' + number).hide();
                            $('#lead_filter_multi_div_' + number).hide();
                            $('#lead_filter_date_picker_div_' + number).hide();
                            $('#lead_filter_fromto_date_picker_div_' + number).hide();
                        }
                    } else {
                        $('#lead_filter_div_' + number).show();
                        $('#lead_filter_multi_div_' + number).hide();
                        $('#lead_filter_text_field_div_' + number).hide();
                        $('#lead_filter_date_picker_div_' + number).hide();
                        $('#lead_filter_fromto_date_picker_div_' + number).hide();
                    }
                    if (isedit == 1) {
                        $('#lead_filter_select_value_' + number).empty().trigger('change');
                        var newOption = new Option(data['value'][0]['text'], data['value'][0]['id'], false, false);
                        $('#lead_filter_select_value_' + number).append(newOption).trigger('change');
                    }
                } else if (arr_filter_condition == "multi_select") {

                    if (arr_filter_column_code == "leads_source" && ischangesourcetype == 1 && $(
                            '#lead_filter_source_type_value_' + number).val() != null) {
                        source_type = $('#lead_filter_source_type_value_' + number).val();
                        if (source_type.split("-")[0] == "textrequired") {
                            $('#lead_filter_text_field_div_' + number).show();
                            $('#lead_filter_div_' + number).hide();
                            $('#lead_filter_multi_div_' + number).hide();
                            $('#lead_filter_date_picker_div_' + number).hide();
                            $('#lead_filter_fromto_date_picker_div_' + number).hide();

                        } else if (source_type.split("-")[0] == "textnotrequired") {

                            $('#lead_filter_text_field_div_' + number).show();
                            $('#lead_filter_div_' + number).hide();
                            $('#lead_filter_multi_div_' + number).hide();
                            $('#lead_filter_date_picker_div_' + number).hide();
                            $('#lead_filter_fromto_date_picker_div_' + number).hide();

                        } else if (source_type.split("-")[0] == "fix") {
                            $('#lead_filter_text_field_div_' + number).show();
                            $('#lead_filter_div_' + number).hide();
                            $('#lead_filter_multi_div_' + number).hide();
                            $('#lead_filter_date_picker_div_' + number).hide();
                            $('#lead_filter_fromto_date_picker_div_' + number).hide();

                            $('#lead_filter_value_' + number).val('-');
                            $('#lead_filter_value_' + number).prop('readonly', true);

                        } else {
                            $('#lead_filter_div_' + number).hide();
                            $('#lead_filter_text_field_div_' + number).hide();
                            $('#lead_filter_multi_div_' + number).show();
                            $('#lead_filter_date_picker_div_' + number).hide();
                            $('#lead_filter_fromto_date_picker_div_' + number).hide();
                        }
                    } else {
                        $('#lead_filter_div_' + number).hide();
                        $('#lead_filter_multi_div_' + number).show();
                        $('#lead_filter_text_field_div_' + number).hide();
                        $('#lead_filter_date_picker_div_' + number).hide();
                        $('#lead_filter_fromto_date_picker_div_' + number).hide();
                    }
                    if (isedit == 1) {
                        $('#lead_filter_select_value_multi_' + number).empty().trigger('change');
                        var selectedSaleval = [];
                        $.each(data['value'], function(key, val) {
                            selectedSaleval.push('' + val['id'] + '');
                            var newOption = new Option(val['text'], val['id'], false, false);
                            $('#lead_filter_select_value_multi_' + number).append(newOption).trigger('change');
                        });
                        $('#lead_filter_select_value_multi_' + number).val(selectedSaleval).change();
                    }
                }


            } else if (arr_filter_column == "reward_select") {
                var arr_filter_condition = "{{ json_encode(getFilterCondtionCRM()) }}".replace(/&quot;/g, '"');
                var arr_filter_condition = JSON.parse(arr_filter_condition)[condition_id]['value_type'];

                if (arr_filter_column_code == "leads_source") {
                    $('#lead_filter_div_source_type_' + number).show();
                } else {
                    $('#lead_filter_div_source_type_' + number).hide();
                }
                if (arr_filter_condition == "single_select") {
                    if (arr_filter_column_code == "leads_source" && ischangesourcetype == 1 && $('#lead_filter_source_type_value_' + number).val() != null) {
                        source_type = $('#lead_filter_source_type_value_' + number).val();

                        if (source_type.split("-")[0] == "textrequired") {
                            $('#lead_filter_text_field_div_' + number).show();
                            $('#lead_filter_div_' + number).hide();
                            $('#lead_filter_multi_div_' + number).hide();
                            $('#lead_filter_date_picker_div_' + number).hide();
                            $('#lead_filter_fromto_date_picker_div_' + number).hide();

                        } else if (source_type.split("-")[0] == "textnotrequired") {

                            $('#lead_filter_text_field_div_' + number).show();
                            $('#lead_filter_div_' + number).hide();
                            $('#lead_filter_multi_div_' + number).hide();
                            $('#lead_filter_date_picker_div_' + number).hide();
                            $('#lead_filter_fromto_date_picker_div_' + number).hide();

                        } else if (source_type.split("-")[0] == "fix") {
                            $('#lead_filter_text_field_div_' + number).show();
                            $('#lead_filter_div_' + number).hide();
                            $('#lead_filter_multi_div_' + number).hide();
                            $('#lead_filter_date_picker_div_' + number).hide();
                            $('#lead_filter_fromto_date_picker_div_' + number).hide();

                            $('#lead_filter_value_' + number).val('-');
                            $('#lead_filter_value_' + number).prop('readonly', true);

                        } else {
                            $('#lead_filter_div_' + number).show();
                            $('#lead_filter_text_field_div_' + number).hide();
                            $('#lead_filter_multi_div_' + number).hide();
                            $('#lead_filter_date_picker_div_' + number).hide();
                            $('#lead_filter_fromto_date_picker_div_' + number).hide();
                        }
                    } else {
                        $('#lead_filter_div_' + number).show();
                        $('#lead_filter_multi_div_' + number).hide();
                        $('#lead_filter_text_field_div_' + number).hide();
                        $('#lead_filter_date_picker_div_' + number).hide();
                        $('#lead_filter_fromto_date_picker_div_' + number).hide();
                    }
                    
                    if (isedit == 1) {
                        $('#lead_filter_select_value_' + number).empty().trigger('change');
                        var newOption = new Option(data['value'][0]['text'], data['value'][0]['id'], false, false);
                        $('#lead_filter_select_value_' + number).append(newOption).trigger('change');
                    }
                } 
            } else if (arr_filter_column == "date") {
                var condition_id = $('#selectAdvanceFilterCondtion_' + number).val();
                var arr_filter_condition = "{{ json_encode(getFilterCondtionCRM()) }}".replace(/&quot;/g, '"');
                var arr_filter_condition = JSON.parse(arr_filter_condition)[condition_id]['value_type'];

                if (arr_filter_condition == "single_select") {
                    $('#lead_filter_date_picker_div_' + number).show();
                    $('#lead_filter_div_' + number).hide();
                    $('#lead_filter_multi_div_' + number).hide();
                    $('#lead_filter_fromto_date_picker_div_' + number).hide();
                    $('#lead_filter_div_source_type_' + number).hide();
                    // if (isedit == 1) {
                    //     $('#lead_filter_date_picker_value_' + number).val(data['value'][0]['text']);
                    // }
                    // $('#lead_filter_div_' + number).show();
                    // $('#lead_filter_multi_div_' + number).hide();
                    // $('#lead_filter_text_field_div_' + number).hide();
                    // $('#lead_filter_date_picker_div_' + number).hide();
                    // $('#lead_filter_fromto_date_picker_div_' + number).hide();

                    // $('#lead_filter_select_value_' + number).select2({minimumInputLength: 0});
                    // $('#lead_filter_select_value_' + number).data("select2").opts.minimumInputLength = 0


                    if (isedit == 1) {
                        $('#lead_filter_date_picker_value_' + number).empty().trigger('change');
                        var newOption = new Option(data['value'][0]['text'], data['value'][0]['id'], false, false);
                        $('#lead_filter_date_picker_value_' + number).append(newOption).trigger('change');
                    }

                } else if (arr_filter_condition == "between") {
                    $('#lead_filter_fromto_date_picker_div_' + number).show();
                    $('#lead_filter_div_' + number).hide();
                    $('#lead_filter_multi_div_' + number).hide();
                    $('#lead_filter_date_picker_div_' + number).hide();
                    $('#lead_filter_div_source_type_' + number).hide();
                    if (isedit == 1) {
                        date = data['value'][0]['text'].split(',');
                        $('#lead_filter_from_date_picker_value_' + number).val(date[0]);
                        $('#lead_filter_to_date_picker_value_' + number).val(date[1]);
                    }
                }
                $('#lead_filter_text_field_div_' + number).hide();

            } else {
                $('#lead_filter_text_field_div_' + number).show();
                $('#lead_filter_div_' + number).hide();
                $('#lead_filter_multi_div_' + number).hide();
                $('#lead_filter_date_picker_div_' + number).hide();
                $('#lead_filter_fromto_date_picker_div_' + number).hide();
                $('#lead_filter_div_source_type_' + number).hide();
                if (isedit == 1) {
                    $('#lead_filter_value_' + number).val(data['value'][0]['text']);
                }
            }
        }
    }

    function addNLoadfilter(isedit = 0, data = null) {
        var addAdvanceFilterRows = '<div class="d-flex align-items-center border-top pt-1">';
        addAdvanceFilterRows += '<div class="row flex-nowrap align-items-center filterrow flex-fill">';
        addAdvanceFilterRows += '<div class="col-2 ps-0">';
        addAdvanceFilterRows += '<div class="mb-1 mt-lg-0">';
        addAdvanceFilterRows +=
            '<select class="form-control" id="selectAdvanceFilterClause_' + filter_count +
            '" name="selectAdvanceFilterClause_' + filter_count + '" required>';
        @foreach (getFilterClauseCRM() as $filt)
            if (isedit == 1) {
                clause_selected = data['clause_id'] == {{ $filt['id'] }} ? 'selected' : '';
            } else {
                clause_selected = '';
            }
            addAdvanceFilterRows += '<option value="{{ $filt['id'] }}" ' + clause_selected +
                ' >{{ $filt['name'] }}</option>';
        @endforeach
        addAdvanceFilterRows += '</select>';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '<div class="col-3 ps-0">';
        addAdvanceFilterRows += '<div class="mb-1 mt-lg-0">';
        addAdvanceFilterRows += '<input type="hidden" filt_id="' + filter_count + '" name="multi_filter_loop">';

        addAdvanceFilterRows +=
            '<select class="form-control" id="selectAdvanceFilterColumn_' + filter_count +
            '" name="selectAdvanceFilterColumn_' + filter_count + '" required>';
        @foreach (getFilterColumnCRM() as $filt)
            if (isedit == 1) {
                column_selected = data['column_id'] == {{ $filt['id'] }} ? 'selected' : '';
            } else {
                column_selected = '';
            }
            addAdvanceFilterRows += '<option value="{{ $filt['id'] }}" ' + column_selected +
                '>{{ $filt['name'] }}</option>';
        @endforeach
        addAdvanceFilterRows += '</select>';

        addAdvanceFilterRows += '<div class="invalid-feedback">';
        addAdvanceFilterRows += 'Please select Condtion.';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '<div class="invalid-feedback">Please select Column.</div>';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '<div class="col-3 ps-0">';
        addAdvanceFilterRows += '<div class="mb-1 mt-lg-0">';
        addAdvanceFilterRows += '<select class="form-control" id="selectAdvanceFilterCondtion_' + filter_count + '"';
        addAdvanceFilterRows += 'name="selectAdvanceFilterCondtion_' + filter_count + '" required>';
        addAdvanceFilterRows += '</select>';
        addAdvanceFilterRows += '<div class="invalid-feedback">Please select Condtion.';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '<div class="col-4 ps-0">';
        addAdvanceFilterRows += '<div class="mb-1 mt-lg-0" id="lead_filter_div_source_type_' + filter_count +
            '" style="display: none;">';
        addAdvanceFilterRows += '<div class="col-md-12">';
        addAdvanceFilterRows += '<div class="ajax-select mt-lg-0">';
        addAdvanceFilterRows += '<select class="form-control select2-ajax"';
        addAdvanceFilterRows += 'id="lead_filter_source_type_value_' + filter_count + '"';
        addAdvanceFilterRows += 'name="lead_filter_source_type_value_' + filter_count + '">';
        addAdvanceFilterRows += '</select>';
        addAdvanceFilterRows += '<div class="invalid-feedback">Please select type.</div>';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '<div class="mb-1 mt-lg-0" id="lead_filter_div_' + filter_count +
            '" style="display: none;">';
        addAdvanceFilterRows += '<div class="col-md-12">';
        addAdvanceFilterRows += '<div class="ajax-select mt-lg-0">';
        addAdvanceFilterRows += '<select class="form-control select2-ajax" id="lead_filter_select_value_' +
            filter_count + '" name="lead_filter_select_value_' + filter_count + '">';
        addAdvanceFilterRows += '</select>';
        addAdvanceFilterRows += '<div class="invalid-feedback">Please select value.</div>';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '</div>';

        addAdvanceFilterRows += '<div class="mb-1 mt-lg-0" id="lead_filter_multi_div_' + filter_count +
            '" style="display: none;">';
        addAdvanceFilterRows += '<div class="col-md-12">';
        addAdvanceFilterRows += '<div class="ajax-select mt-lg-0">';
        addAdvanceFilterRows +=
            '<select class="form-control select2-ajax select2-multiple" multiple="multiple" id="lead_filter_select_value_multi_' +
            filter_count + '" name="lead_filter_select_value_multi_' + filter_count + '[]" required>';
        addAdvanceFilterRows += '</select>';
        addAdvanceFilterRows += '<div class="invalid-feedback">Please select value.</div>';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '<div class="mb-1 mt-lg-0" style="display: none;" id="lead_filter_text_field_div_' +
            filter_count + '">';
        addAdvanceFilterRows += '<div class="col-md-12">';
        addAdvanceFilterRows += '<input type="text" class="form-control" id="lead_filter_value_' + filter_count + '" ';
        addAdvanceFilterRows += 'name="lead_filter_value_' + filter_count + '" placeholder="Value" value="" required>';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '<div class="mb-1 mt-lg-0" id="lead_filter_date_picker_div_' + filter_count +
            '" style="display: none;">';
        addAdvanceFilterRows += '<div class="col-md-12">';
        // addAdvanceFilterRows += '<input autocomplete="off" type="text" class="form-control" data-date-format="dd-mm-yyyy" data-date-container="#filterdropdownmodel" data-provide="datepicker" data-date-autoclose="true" required ';
        // addAdvanceFilterRows += ' id="lead_filter_date_picker_value_' + filter_count + '" name="lead_filter_date_picker_value_' + filter_count + '"';
        // addAdvanceFilterRows += ' placeholder="Select Date" value="' + '{{ date('d-m-Y') }}' + '">';
        addAdvanceFilterRows += '<div class="ajax-select mt-lg-0">';
        addAdvanceFilterRows += '<select class="form-control select2-ajax" id="lead_filter_date_picker_value_' +
            filter_count + '" name="lead_filter_date_picker_value_' + filter_count + '">';
        addAdvanceFilterRows += '</select>';
        addAdvanceFilterRows += '<div class="invalid-feedback">Please select value.</div>';
        addAdvanceFilterRows += '</div>';

        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '<div class="row mb-1 mt-lg-0" id="lead_filter_fromto_date_picker_div_' + filter_count +
            '" style="display: none;">';
        addAdvanceFilterRows += '<div class="col-md-6 pe-0">';
        addAdvanceFilterRows +=
            '<input autocomplete="off" type="text" class="form-control" data-date-format="dd-mm-yyyy" data-date-container="#filterdropdownmodel" data-provide="datepicker" data-date-autoclose="true" required';
        addAdvanceFilterRows += ' id="lead_filter_from_date_picker_value_' + filter_count + '"';
        addAdvanceFilterRows += ' name="lead_filter_from_date_picker_value_' + filter_count +
            '" placeholder="Select Date" value="' + '{{ date('d-m-Y') }}' + '">';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '<div class="col-md-6 ps-1">';
        addAdvanceFilterRows +=
            '<input autocomplete="off" type="text" class="form-control" data-date-format="dd-mm-yyyy" data-date-container="#filterdropdownmodel" data-provide="datepicker" data-date-autoclose="true" required';
        addAdvanceFilterRows += ' id="lead_filter_to_date_picker_value_' + filter_count +
            '" name="lead_filter_to_date_picker_value_' + filter_count + '"';
        addAdvanceFilterRows += ' placeholder="Select Date" value="' + '{{ date('d-m-Y') }}' + '">';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows +=
            '<div class="p-0 remove d-flex justify-content-end" style="cursor: pointer;width:30px;">';
        addAdvanceFilterRows += '<i class="bx bx-x-circle" style="font-size: large;"></i>';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '</div>';

        $("#advanceFilterRows").append(addAdvanceFilterRows);

        var new_filter_count = filter_count;
        $("#selectAdvanceFilterClause_" + new_filter_count).select2();
        $("#selectAdvanceFilterColumn_" + new_filter_count).select2().on('change', function(e) {
            oncolumnNFunctionChange(new_filter_count);

        });

        $("#lead_filter_source_type_value_" + new_filter_count).select2({
            ajax: {
                url: ajaxURLSearchFilterSourceTypeValue,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                        is_advance_filter: 0,
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
            placeholder: 'select type',
            dropdownParent: $("#filterdropdownmodel"),
        }).on('change', function(e) {
            oncolumnNFunctionChange(new_filter_count, 0, null, null, 1);
        });

        $("#lead_filter_select_value_" + new_filter_count).select2({
            ajax: {
                url: ajaxURLSearchFilterValue,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                        @if (isset($data['is_deal']))
                            is_deal: {{ $data['is_deal'] }},
                        @endif
                        column: function() {
                            return $("#selectAdvanceFilterColumn_" + new_filter_count).val()
                        },
                        condtion: function() {
                            return $("#selectAdvanceFilterCondtion_" + new_filter_count).val()
                        },
                        source_type: function() {
                            return $("#lead_filter_source_type_value_" + new_filter_count).val()
                        },

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
            placeholder: 'Please select value',
            // minimumInputLength: 2,
            dropdownParent: $("#filterdropdownmodel"),
        });
        $("#lead_filter_date_picker_value_" + new_filter_count).select2({
            ajax: {
                url: ajaxURLSearchFilterValue,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                        @if (isset($data['is_deal']))
                            is_deal: {{ $data['is_deal'] }},
                        @endif
                        column: function() {
                            return $("#selectAdvanceFilterColumn_" + new_filter_count).val()
                        },
                        condtion: function() {
                            return $("#selectAdvanceFilterCondtion_" + new_filter_count).val()
                        },
                        source_type: function() {
                            return $("#lead_filter_source_type_value_" + new_filter_count).val()
                        },

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
            placeholder: 'Please select value',
            dropdownParent: $("#filterdropdownmodel"),
        });

        $("#lead_filter_select_value_multi_" + new_filter_count).select2({
            ajax: {
                url: ajaxURLSearchFilterValue,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                        is_deal: {{ $data['is_deal'] }},
                        column: function() {
                            return $("#selectAdvanceFilterColumn_" + new_filter_count).val()
                        },
                        condtion: function() {
                            return $("#selectAdvanceFilterCondtion_" + new_filter_count).val()
                        },
                        source_type: function() {
                            return $("#lead_filter_source_type_value_" + new_filter_count).val()
                        },
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
            placeholder: 'Please select value',
            // minimumInputLength: 2,
            dropdownParent: $("#filterdropdownmodel"),
        });

        $("#selectAdvanceFilterCondtion_" + new_filter_count).select2({
            ajax: {
                url: ajaxURLSearchFilterCondition,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                        column: function() {
                            return $("#selectAdvanceFilterColumn_" + new_filter_count).val()
                        },
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
            placeholder: 'select condition',
            dropdownParent: $("#filterdropdownmodel"),
        }).on('change', function(e) {
            oncolumnNFunctionChange(new_filter_count);
        });



        $('#advanceFilterInfo').text("(no.of filter : " + (filter_count + 1) + ")");


        filter_count++;
    }

    // $("#view_advance_filter").select2({
    //     placeholder: 'Search for Filter View',
    //     ajax: {
    //         url: ajaxSearchAdvanceFilterView,
    //         dataType: 'json',
    //         delay: 0,
    //         data: function(params) {
    //             return {
    //                 q: params.term, // search term
    //                 page: params.page,
    //                 is_deal: function(params) {
    //                     return $('#is_deal_hidden').val();
    //                 }
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
    //     escapeMarkup: function(markup) {
    //         return markup;
    //     },
    //     dropdownParent: $("#top-menu-lead")

    // }).on('change', function(e) {

    //     if ($('#view_advance_filter').val() != null) {
    //         $('#list_data_loader').show();
    //         $.ajax({
    //             type: 'POST',
    //             url: ajaxGetAdvanceFilterViewDetail,
    //             data: {
    //                 "view_id": $('#view_advance_filter').val(),
    //                 '_token': $("[name=_token]").val()
    //             },
    //             success: function(responseText) {
    //                 if (responseText['status'] == 1) {
    //                     // toastr["success"](responseText['msg']);
    //                     clearAllFilter().done(function() {
    //                         filter_count = 1;
    //                         $.each(responseText['filter_item'], function(key, val) {

    //                             if (key != 0) {
    //                                 addNLoadfilter(1, val);
    //                             }
    //                             oncolumnNFunctionChange(key, 1, val, responseText[
    //                                 'filter']);

    //                         });
    //                         ischeckFilter();
    //                     });


    //                 } else {
    //                     toastr["error"](responseText['msg']);
    //                 }
    //             }
    //         })
    //     }

    // });
    // $(document).ready(function() {
    //     var default_filter_id = {{ $data['default_filter']['id'] }};
    //     var default_filter_text = "{{ $data['default_filter']['text'] }}";

    //     if (default_filter_id != 0) {
    //         // $('#view_advance_filter').empty().trigger('change');
    //         // var newOption = new Option(default_filter_text, default_filter_id, false, false);
    //         // $('#view_advance_filter').append(newOption).trigger('change');

    //         $('#view_advance_filter').html('<option value="' + default_filter_id + '">' + default_filter_text + '</option>');

    //         // Render HTML in the selected option's text
    //         $('#view_advance_filter').on('select2:select', function(e) {
    //             var selectedOption = $(this).find(':selected');
    //             var renderedHtml = selectedOption.text(); // Customize the HTML rendering as needed
    //             selectedOption.html(renderedHtml);
    //         });

    //     }
    // });

    // function setViewAsFavorite(view_id) {
    //     console.log(view_id);
    //     $.ajax({
    //         type: 'POST',
    //         url: ajaxURLSaveFilterViewAsDefault,
    //         data: {
    //             "view_id": view_id,
    //             '_token': $("[name=_token]").val()
    //         },
    //         success: function(responseText) {
    //             if (responseText['status'] == 1) {
    //                 toastr["success"](responseText['msg']);
    //                 $('#modalView').modal('hide');
    //             } else {
    //                 toastr["error"](responseText['msg']);
    //             }
    //             $("#saveFilterAsView").html('Save');
    //         }
    //     })
    // }

    $('.view_advance_filter').on('click', function() {
        console.log($(this).attr('data-value'));
        if ($(this).attr('data-value') != null) {
            $('#list_data_loader').show();
            $.ajax({
                type: 'POST',
                url: ajaxGetAdvanceFilterViewDetail,
                data: {
                    "view_id": $(this).attr('data-value'),
                    '_token': $("[name=_token]").val()
                },
                success: function(responseText) {
                    if (responseText['status'] == 1) {
                        // toastr["success"](responseText['msg']);
                        clearAllFilter().done(function() {
                            filter_count = 1;
                            $.each(responseText['filter_item'], function(key, val) {

                                if (key != 0) {
                                    addNLoadfilter(1, val);
                                }
                                oncolumnNFunctionChange(key, 1, val, responseText[
                                    'filter']);

                            });
                            reloadLeadList(0, 1)
                            ischeckFilter();
                        });


                    } else {
                        toastr["error"](responseText['msg']);
                    }
                }
            })
        }

    });



    $(document).ready(function() {
        var default_filter_id = {{ $data['default_filter']['id'] }};
        var default_filter_text = '{!! $data['default_filter']['text'] !!}';

        $('#advance-filter-view').html(default_filter_text);

        $('#list_data_loader').show();
        $.ajax({
            type: 'POST',
            url: ajaxGetAdvanceFilterViewDetail,
            data: {
                "view_id": default_filter_id,
                '_token': $("[name=_token]").val()
            },
            success: function(responseText) {
                if (responseText['status'] == 1) {
                    // toastr["success"](responseText['msg']);
                    clearAllFilter().done(function() {
                        filter_count = 1;
                        $.each(responseText['filter_item'], function(key, val) {

                            if (key != 0) {
                                addNLoadfilter(1, val);
                            }
                            oncolumnNFunctionChange(key, 1, val, responseText[
                                'filter']);

                        });
                        reloadLeadList(0, 1)
                        ischeckFilter();
                    });


                } else {}
            }
        })
    });


    function setViewAsFavorite(view_id) {
        $.ajax({
            type: 'POST',
            url: ajaxURLSaveFilterViewAsDefault,
            data: {
                "view_id": view_id,
                '_token': $("[name=_token]").val()
            },
            success: function(responseText) {
                if (responseText['status'] == 1) {
                    toastr["success"](responseText['msg']);
                    $('#modalView').modal('hide');
                    AdvanceFilterViewText(view_id);
                } else {
                    toastr["error"](responseText['msg']);
                }
                $("#saveFilterAsView").html('Save');
            }
        })
    }

    function filterListLoad() {
        $.ajax({
            type: 'GET',
            url: ajaxSearchAdvanceFilterView,
            data: {
                "is_deal": $('#is_deal_hidden').val(),
            },
            success: function(responseText) {
                if (responseText['status'] == 1) {
                    var drop_down_value = "";
                    responseText['data'].forEach(value => {
                        drop_down_value += value['name'];
                    });
                    $('#advance-filter-view-content').html(drop_down_value);
                } else {
                    toastr["error"](responseText['msg']);
                }
            }
        })
    }

    $(document).ready(function() {
        filterListLoad();
    })

    $('#advance_filter_search').on('keyup', function() {
        var search_value = $(this).val();

        $.ajax({
            type: 'GET',
            url: ajaxSearchAdvanceFilterView,
            data: {
                "is_deal": $('#is_deal_hidden').val(),
                "q": search_value,
            },
            success: function(responseText) {
                if (responseText['status'] == 1) {
                    var drop_down_value = "";
                    responseText['data'].forEach(value => {
                        drop_down_value += value['name'];
                    });
                    $('#advance-filter-view-content').html(drop_down_value);
                } else {
                    toastr["error"](responseText['msg']);
                }
            }
        })
    })


    function AdvanceFilterViewText(view_id) {
        $.ajax({
            type: 'GET',
            url: ajaxURLViewSelectedFilter,
            data: {
                "view_id": view_id,
            },
            success: function(responseText) {
                if (responseText['status'] == 1) {
                    $('#advance-filter-view').html(responseText['data']['text']);

                    $('#list_data_loader').show();
                    filterListLoad();
                    $.ajax({
                        type: 'POST',
                        url: ajaxGetAdvanceFilterViewDetail,
                        data: {
                            "view_id": responseText['data']['id'],
                            '_token': $("[name=_token]").val()
                        },
                        success: function(responseText) {
                            if (responseText['status'] == 1) {
                                // toastr["success"](responseText['msg']);
                                clearAllFilter().done(function() {
                                    filter_count = 1;
                                    $.each(responseText['filter_item'], function(key,
                                        val) {

                                        if (key != 0) {
                                            addNLoadfilter(1, val);
                                        }
                                        oncolumnNFunctionChange(key, 1, val,
                                            responseText[
                                                'filter']);

                                    });
                                    reloadLeadList(0, 1)
                                    ischeckFilter();
                                });


                            } else {
                                toastr["error"](responseText['msg']);
                            }
                        }
                    })
                } else {
                    toastr["error"](responseText['msg']);
                }
            }
        })
    }

    function AdvanceFilterDelete(view_id) {
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
                        url: ajaxURLAdvanceFilterDelete,
                        data: {
                            "view_id": view_id,
                        },
                        success: function(responseText) {
                            if (responseText['status'] == 1) {
                                toastr["success"](responseText['msg']);
                                filterListLoad();
                                t();
                            } else {
                                toastr["error"](responseText['msg']);
                            }
                        }
                    })
                })
            },
        }).then(function(t) {
            if (t.value === true) {
                Swal.fire({
                    title: "Mark as deleted!",
                    text: "Your Filter View has been Deleted.",
                    icon: "success"
                });


            }

        });



    }
</script>
