<script type="text/javascript">
    var ajaxURLSearchFilterCondition = "{{ route('user.filter.search.condition') }}";
    var ajaxURLSearchFilterValue = "{{ route('user.filter.search.value') }}";
    var ajaxURLSearchFilterSourceTypeValue = "{{ route('crm.lead.search.source.type') }}";

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
                    is_deal: 1,
                    column: function() {
                        return $("#selectAdvanceFilterColumn_0").val()
                    },
                    condtion: function() {
                        return $("#selectAdvanceFilterCondtion_0").val()
                    },
                    is_architect: function() {
                        return $("#is_architect").val()
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

    $("#lead_filter_date_picker_value_0").select2({
        ajax: {
            url: ajaxURLSearchFilterValue,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    is_deal: 1,
                    column: function() {
                        return $("#selectAdvanceFilterColumn_0").val()
                    },
                    condtion: function() {
                        return $("#selectAdvanceFilterCondtion_0").val()
                    },
                    is_architect: function() {
                        return $("#is_architect").val()
                    },
                    source_type: function() {
                        return $("#lead_filter_source_type_value_0").val()
                    },

                };
            },
            processResults: function(data, params) {
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
                    is_deal: 1,
                    column: function() {
                        return $("#selectAdvanceFilterColumn_0").val()
                    },
                    condtion: function() {
                        return $("#selectAdvanceFilterCondtion_0").val()
                    },
                    is_architect: function() {
                        return $("#is_architect").val()
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
                    is_architect: function() {
                        return $("#is_architect").val()
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

    function oncolumnNFunctionChange(number = 0, isedit = 0, data = null, filterval = null, ischangesourcetype = 0) {
        if (isedit == 1) {

            $('#selectAdvanceFilterColumn_' + number).empty().trigger('change');
            var newOption = new Option(data['column_text'], data['column_id'], false, false);
            $('#selectAdvanceFilterColumn_' + number).append(newOption).trigger('change');

            $('#selectAdvanceFilterCondtion_' + number).empty().trigger('change');
            var newOption = new Option(data['condtion_text'], data['condtion_id'], false, false);
            $('#selectAdvanceFilterCondtion_' + number).append(newOption).trigger('change');
        }

        var column_id = $('#selectAdvanceFilterColumn_' + number).val();
        var condition_id = $('#selectAdvanceFilterCondtion_' + number).val();

        if (condition_id != null && column_id != null) {
            if ($('#is_architect').val() == 1) {
                var obj_filter_column = "{{ json_encode(getArchitectFilterColumn()) }}".replace(/&quot;/g, '"');
            } else if ($('#is_architect').val() == 0) {
                var obj_filter_column = "{{ json_encode(getElectricianFilterColumn()) }}".replace(/&quot;/g, '"');
            }
            var arr_filter_column = JSON.parse(obj_filter_column)[column_id]['value_type'];
            var arr_filter_column_code = JSON.parse(obj_filter_column)[column_id]['code'];
            if (ischangesourcetype == 0) {
                $('#lead_filter_source_type_value_' + number).empty().trigger('change');
                if (isedit == 1) {
                    $('#lead_filter_source_type_value_' + number).empty().trigger('change');
                    var newOption = new Option(data['source_type_text'], data['source_type_id'], false, false);
                    $('#lead_filter_source_type_value_' + number).append(newOption).trigger('change');
                }
            }

            console.log(arr_filter_column);
            $('#lead_filter_select_value_' + number).empty().trigger('change');
            $('#lead_filter_select_value_multi_' + number).empty().trigger('change');

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

                if (arr_filter_column_code == "user_source") {
                    $('#lead_filter_div_source_type_' + number).show();
                } else {
                    $('#lead_filter_div_source_type_' + number).hide();
                }

                if (arr_filter_condition == "single_select") {

                    
                    if (arr_filter_column_code == "user_source" && ischangesourcetype == 1 && $('#lead_filter_source_type_value_' + number).val() != null) { 
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
                    if (arr_filter_column_code == "user_source" && ischangesourcetype == 1 && $('#lead_filter_source_type_value_' + number).val() != null) {
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

            } else if (arr_filter_column == "select_order_by") {
                var condition_id = $('#selectAdvanceFilterCondtion_' + number).val();
                var arr_filter_condition = "{{ json_encode(getFilterCondtionCRM()) }}".replace(/&quot;/g, '"');
                var arr_filter_condition = JSON.parse(arr_filter_condition)[condition_id]['value_type'];

                if (arr_filter_condition == "single_select") {

                    $('#lead_filter_div_' + number).show();
                    $('#lead_filter_multi_div_' + number).hide();
                    $('#lead_filter_text_field_div_' + number).hide();
                    $('#lead_filter_date_picker_div_' + number).hide();
                    $('#lead_filter_fromto_date_picker_div_' + number).hide();

                    if (isedit == 1) {
                        $('#lead_filter_select_value_' + number).empty().trigger('change');
                        var newOption = new Option(data['value'][0]['text'], data['value'][0]['id'], false, false);
                        $('#lead_filter_select_value_' + number).append(newOption).trigger('change');
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

    var filter_count = 1;
    $("#btnAddAdvanceFilter").click(function(event) {
        event.preventDefault();
        addNLoadfilter();
    });

    function addNLoadfilter(isedit = 0, data = null) {
        var is_architect = $("#is_architect").val();
        console.log(is_architect);
        var addAdvanceFilterRows = '<div class="d-flex align-items-center border-top pt-1">';
        addAdvanceFilterRows += '<div class="row flex-nowrap align-items-center filterrow flex-fill">';
        addAdvanceFilterRows += '<div class="col-2 ps-0">';
        addAdvanceFilterRows += '<div class="mb-1 mt-lg-0">';
        addAdvanceFilterRows +=
            '<select class="form-control" id="selectAdvanceFilterClause_' + filter_count +
            '" name="selectAdvanceFilterClause_' + filter_count + '" required>';
        @foreach (getUserFilterClause() as $filt)
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

        if (is_architect == 1) {
            addAdvanceFilterRows +=
                '<select class="form-control" id="selectAdvanceFilterColumn_' + filter_count +
                '" name="selectAdvanceFilterColumn_' + filter_count + '" required>';
            @foreach (getArchitectFilterColumn() as $filt)
                if (isedit == 1) {
                    column_selected = data['column_id'] == {{ $filt['id'] }} ? 'selected' : '';
                } else {
                    column_selected = '';
                }
                addAdvanceFilterRows += '<option value="{{ $filt['id'] }}" ' + column_selected +
                    '>{{ $filt['name'] }}</option>';
            @endforeach
            addAdvanceFilterRows += '</select>';
        } else if (is_architect == 0) {
            addAdvanceFilterRows +=
                '<select class="form-control" id="selectAdvanceFilterColumn_' + filter_count +
                '" name="selectAdvanceFilterColumn_' + filter_count + '" required>';

            @foreach (getElectricianFilterColumn() as $filt)
                if (isedit == 1) {
                    column_selected = data['column_id'] == {{ $filt['id'] }} ? 'selected' : '';
                } else {
                    column_selected = '';
                }
                addAdvanceFilterRows += '<option value="{{ $filt['id'] }}" ' + column_selected +
                    '>{{ $filt['name'] }}</option>';
            @endforeach
            addAdvanceFilterRows += '</select>';
        }



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
        addAdvanceFilterRows += '<div class="mb-1 mt-lg-0" id="lead_filter_div_source_type_' + filter_count + '" style="display: none;">';
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
        addAdvanceFilterRows += '<div class="mb-1 mt-lg-0" id="lead_filter_div_' + filter_count + '" style="display: none;">';
        addAdvanceFilterRows += '<div class="col-md-12">';
        addAdvanceFilterRows += '<div class="ajax-select mt-lg-0">';
        addAdvanceFilterRows += '<select class="form-control select2-ajax" id="lead_filter_select_value_' + filter_count + '" name="lead_filter_select_value_' + filter_count + '">';
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

        $("#lead_filter_select_value_" + new_filter_count).select2({
            ajax: {
                url: ajaxURLSearchFilterValue,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                        column: function() {
                            return $("#selectAdvanceFilterColumn_" + new_filter_count).val()
                        },
                        condtion: function() {
                            return $("#selectAdvanceFilterCondtion_" + new_filter_count).val()
                        },
                        is_architect: function() {
                            return $("#is_architect").val()
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

        $("#lead_filter_date_picker_value_" + new_filter_count).select2({
            ajax: {
                url: ajaxURLSearchFilterValue,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                        column: function() {
                            return $("#selectAdvanceFilterColumn_" + new_filter_count).val()
                        },
                        condtion: function() {
                            return $("#selectAdvanceFilterCondtion_" + new_filter_count).val()
                        },
                        is_architect: function() {
                            return $("#is_architect").val()
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
                        column: function() {
                            return $("#selectAdvanceFilterColumn_" + new_filter_count).val()
                        },
                        condtion: function() {
                            return $("#selectAdvanceFilterCondtion_" + new_filter_count).val()
                        },
                        is_architect: function() {
                            return $("#is_architect").val()
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
                        is_architect: function() {
                            return $("#is_architect").val()
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

        $("#lead_filter_source_type_value_" + new_filter_count).select2({
            ajax: {
                url: ajaxURLSearchFilterSourceTypeValue,
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
            placeholder: 'select type',
            dropdownParent: $("#filterdropdownmodel"),
        }).on('change', function(e) {
            oncolumnNFunctionChange(new_filter_count, 0, null, null, 1);
        });



        $('#advanceFilterInfo').text("(no.of filter : " + (filter_count + 1) + ")");


        filter_count++;
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
            $('#advance-filter-view').html(
                '<div><label class="star-radio d-flex align-items-center justify-content-between"><span>Select View</span><i class="bx bxs-right-arrow"></i></label></div>'
                );
        } else {
            deferred.resolve();
        }
        ischeckFilter();
        return deferred.promise();
    }

    $("#btnClearAdvanceFilter").click(function(event) {
        event.preventDefault();
        clearAllFilter(1);
    });

    function ischeckFilter(isfilter = 0) {
        if (filter_count > 1) {
            $('#isfiltercount').show();
            $('#isfiltercount').text(filter_count);

        } else if (filter_count == 1) {
            var selectValue = $("#lead_filter_select_value_0");
            var selectmultiValue = $("#lead_filter_select_value_multi_0");
            var selectFromDateValue = $("#lead_filter_from_date_picker_value_0");
            var selectToDateValue = $("#lead_filter_to_date_picker_value_0");
            var selecttextValue = $("#lead_filter_value_0");
            if (selectValue.val() == null || selectValue.val() === ""
            || selectmultiValue.val() == null || selectmultiValue.val() === ""
            || selectFromDateValue.val() == null || selectFromDateValue.val() === ""
            || selectToDateValue.val() == null || selectToDateValue.val() === ""
            || selecttextValue.val() == null || selecttextValue.val() === "") {
                $('#isfiltercount').hide();
                $('#isfiltercount').text(filter_count);
            }else{
                $('#isfiltercount').show();
                $('#isfiltercount').text(filter_count);
            }
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
</script>
