@extends('layouts.main')
@section('title', $data['title'])
@section('content')

<style type="text/css">
    td p {
        max-width: 100%;
        white-space: break-spaces;
        word-break: break-all;
    }

    #imgPreview {
        width: 100% !important;
        height: 100% !important;
    }

    #div_q_price_item_image {
        width: 100px;
        height: 100px;
        padding: 4px;
        margin: 0 auto;
        cursor: pointer;
    }
</style>
<link href="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css')}}" rel="stylesheet" type="text/css">

<div class="page-content">
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Discount Flow Master</h4>
                    <div class="page-title-right">
                        <button id="updateDiscountData" class="btn btn-primary waves-effect waves-light" role="button"><i class="bx bx-plus font-size-16 align-middle me-2"></i>Add Discount</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body" id="discount_listview">
                        <table id="datatable" class="table table-striped dt-responsive  nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Name</th>
                                    <th>Default Discount</th>
                                    <th>User Discount</th>
                                    <th>Is Active</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div> <!-- end col -->
        </div> <!-- end row -->

    </div>
    <!-- container-fluid -->
</div>
<!-- End Page-content -->

@csrf
@endsection('content')

@section('custom-scripts')
@include('../quotation/master/discount/comman/modal_add_discount')

<script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
<script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>

<script src="{{ asset('assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js') }}"></script>
<script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
<script type="text/javascript">
   
    var ajaxDiscountDataURL='{{route('quot.discount.ajax')}}';
    var ajaxDiscountDeleteURL = '{{route('quot.discount.delete')}}';
    var ajaxDiscountUserTypeSearchURL='{{route('quot.discount.user.type.search')}}';
    var ajaxDiscountUserSearchURL='{{route('quot.discount.user.search')}}';
    var ajaxDiscountDetailURL='{{route('quot.discount.detail')}}';
    var ajaxSaveDetailURL='{{route('quot.discount.save')}}';

    var csrfToken = $("[name=_token").val();

    var mainMasterPageLength = getCookie('mainMasterPageLength') !== undefined ? getCookie('mainMasterPageLength') : 10;
    
    let advanceFilterList = '';
    var table = $('#datatable').DataTable({
        "aoColumnDefs": [{
            "bSortable": false,
            "aTargets": [5]
        }],
        "order": [
            [0, 'desc']
        ],
        "processing": true,
        "pagingType": "full_numbers",
        "serverSide": true,
        "info": true,
        "pageLength": mainMasterPageLength,
        "ajax": {
            "url": ajaxDiscountDataURL,
            "type": "POST",
            "data": {
                "_token": csrfToken,
            }
        },
        "aoColumns": [{
                "mData": "id"
            },
            {
                "mData": "name"
            },
            {
                "mData": "default_discount"
            },
            {
                "mData": "user_discount"
            },
            {
                "mData": "status"
            },
            {
                "mData": "action"
            }
        ]
    });

    function reloadTable() {
        table.ajax.reload(null, false);
    }

    $('#datatable').on('length.dt', function(e, settings, len) {
        setCookie('mainMasterPageLength', len, 100);
    });

    var filter_count = 1;
    $("#btnAddAdvanceFilter").click(function(event) {
        event.preventDefault();
        addNLoadfilter();
    });

    function addNLoadfilter(isedit = 0, data = null, user_type = null, user_id = null, discountData = null) {
        var addAdvanceFilterRows = '<div class="row" style="border-top: 1px solid gainsboro; padding-top: 8px;" id="discount_source_container">';
        addAdvanceFilterRows += '<input type="hidden" filt_id="' + filter_count + '" name="multi_filter_loop">';
        addAdvanceFilterRows += '<div class="col-md-4">';
        addAdvanceFilterRows += '<div class="mb-3 ajax-select mt-3 mt-lg-0">';
        addAdvanceFilterRows +=
            '<select class="form-control" id="discount_user_type_id' + filter_count +
            '" name="discount_user_type_id' + filter_count + '" required>';
        // Add options for user types here
        addAdvanceFilterRows += '</select>';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '<div class="col-md-4">';
        addAdvanceFilterRows += '<div class="mb-3 ajax-select mt-3 mt-lg-0">';
        addAdvanceFilterRows += '<select class="form-control select2-ajax select2-multiple" multiple="multiple" id="discount_user_id' + filter_count + '"';
        addAdvanceFilterRows += 'name="discount_user_id' + filter_count + '[]" required>';
        // Add options for users here
        addAdvanceFilterRows += '</select>';
        addAdvanceFilterRows += '<div class="invalid-feedback">Please select User.';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '<div class="col-md-3">';
        addAdvanceFilterRows += '<div class="mb-3 ajax-select mt-3 mt-lg-0">';
        // Add discount input field here
        addAdvanceFilterRows += '<input type="number" step="0.01" class="form-control" id="discount_dis' + filter_count + '" name="discount_dis' + filter_count + '" placeholder="Discount" value="00.00" required>';
        addAdvanceFilterRows += '<div class="invalid-feedback">Please select discount.</div>';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '</div>';
        
        addAdvanceFilterRows +=
            '<div class="p-0 remove d-flex justify-content-end" style="cursor: pointer;width:30px;">';
        addAdvanceFilterRows += '<i class="bx bx-x-circle" style="font-size: large;"></i>';
        addAdvanceFilterRows += '</div>';
        addAdvanceFilterRows += '</div>';

        $("#advanceFilterRows").append(addAdvanceFilterRows);

        var new_filter_count = filter_count;

        $("#discount_user_type_id" + new_filter_count).select2();
        $("#discount_user_id" + new_filter_count).select2();
        $("#discount_dis" + new_filter_count);

        $("#discount_user_type_id" + new_filter_count).select2({
            ajax: {
                url: ajaxDiscountUserTypeSearchURL,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        id:  function() { return $("#dis_flow_id").val() },
                        q: params.term, // search term
                        page: params.page,
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
            placeholder: 'Search for a user type',
            dropdownParent: $("#modalDiscount"),
        });

        $("#discount_user_type_id" + new_filter_count).on('change', function() {
            $("#discount_user_id" + new_filter_count).empty().trigger('change');
        });

        $("#discount_user_id" + new_filter_count).select2({
            ajax: {
                url: ajaxDiscountUserSearchURL,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        id:  function() { return $("#dis_flow_id").val() },
                        user_type_id: $("#discount_user_type_id" + new_filter_count).val(),
                        q: params.term, // search term
                        page: params.page,
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
            placeholder: 'Search for a user',
            dropdownParent: $("#modalDiscount"),
        });

        if (user_type !== null) {
        var newOption = new Option(user_type['name'], user_type['id'], false, false);
        $('#discount_user_type_id' + filter_count).append(newOption).trigger('change');
        }

        // Set user_id value if provided
        if (user_id !== null) {
            for (var j = 0; j < user_id.length; j++) {
                var newOption = new Option(user_id[j]['text'], user_id[j]['id'], false, false);
                $('#discount_user_id' + filter_count).append(newOption).trigger('change');
            }
        }

        if (discountData !== null && discountData !== undefined) {
            $("#discount_dis" + filter_count).val(discountData);
        }
        
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
        // $('#advanceFilterInfo').text("");
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
            var discount_user_type_id = $('#discount_user_type_id');
            var discount_user_id = $('#discount_user_id');
            var discount_dis = $('#discount_dis');
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

    $('#saveAdvanceFilter').on('click', function() {
        $("#saveAdvanceFilter").html(
            '<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span  >Saving...</span>');
        $('#hidden_status').attr('value', 0);
        // saveLogWithAdvanceFilterList();
        reloadLeadList(0, 1);
        saveProcess();
        ischeckFilter();
    })

    function reloadLeadList(status = 0) {
        // leadtable.settings()[0].jqXHR.abort();
        if (status != 0) {
            clearAllFilter(0);
            $('#advance-filter-view').html(
                '<div><label class="star-radio d-flex align-items-center justify-content-between"><span>Select View</span><i class="bx bxs-right-arrow"></i></label></div>'
            );
            isLeadAmountSummaryRefresh = 0;
            $('#hidden_status').attr('value', status);
        }

        let tempadvanceFilterList = [];

            tempadvanceFilterList.push({
                discount_user_type_id: $('#discount_user_type_id').val(),
                discount_user_id: $('#discount_user_id').val(),
                discount_dis: $('#discount_dis').val()
            });

            $('#advanceFilterRows input[name="multi_filter_loop"]').each(function(ind) {
                let filtValId = $(this).attr("filt_id");
                tempadvanceFilterList.push({
                    discount_user_type_id: $('#discount_user_type_id' + filtValId).val(),
                    discount_user_id: $('#discount_user_id' + filtValId).val(),
                    discount_dis: $('#discount_dis' + filtValId).val()
                });
            });

        advanceFilterList = JSON.stringify(tempadvanceFilterList)
       
    }

    function saveProcess() {
        // Check if advanceFilterList exists and is not null
        if (advanceFilterList !== null) {
            var formData = {
                "_token": csrfToken,
                'isDisName': $('#discount_name').val(),
                'isDisFlow': $('#dis_flow_id').val(),
                'isDisDefault': $('#discount_default_dis').val(),
                'isDisUser': $('#user_default_dis').val(),
                'isDisStatus': $('#discount_status').val(),
                'AdvanceData': advanceFilterList // Include advanceFilterList here
            };
            $.ajax({
                type: 'POST',
                url: ajaxSaveDetailURL,
                data: formData,
                success: function(response) {
                    if (response.status == 1) {
                        toastr["success"](response.msg);
                        reloadTable();
                        resetInputForm();
                        $("#modalDiscount").modal('hide');
                    } else if (response.status == 0) {
                        toastr["error"](response.msg);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error saving log:', error);
                    toastr["error"]("Error occurred while saving the data.");
                }
            });
        } else {
            console.error('Error: advanceFilterList is null.');
        }
    }

    $(document).ready(function() {
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
        $('#formDiscount').ajaxForm(options);
    });

    function showRequest(formData, jqForm, options) {
        // generateHierarchyCode($("#q_subgroup_master_name").val());
        // formData is an array; here we use $.param to convert it to a string to display it
        // but the form plugin does this for you automatically when it submits the data
        var queryString = $.param(formData);

        // jqForm is a jQuery object encapsulating the form element.  To access the
        // DOM element for the form do this:
        // var formElement = jqForm[0];

        // alert('About to submit: \n\n' + queryString);

        // here we could return false to prevent the form from being submitted;
        // returning anything other than false will allow the form submit to continue
        return true;
    }

    function showResponse(responseText, statusText, xhr, $form) {

        if (responseText['status'] == 1) {
            toastr["success"](responseText['msg']);
            reloadTable();
            resetInputForm();
            $("#modalDiscount").modal('hide');
        } else if (responseText['status'] == 0) {
            toastr["error"](responseText['msg']);

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

    $("#updateDiscountData").click(function() {
        $("#modalDiscountLable").html("Add Discount");
        $("#formDiscount").show();
        $(".loadingcls").hide();
        showdiscountDialog();
        resetInputForm();

        source_type_arr = []

        $("#no_of_source").val('1');
        $("#moreSourceDiv").html("");
    });

    $("#discount_status").select2({
        minimumResultsForSearch: Infinity,
        dropdownParent: $("#modalDiscount")
    });

    function showdiscountDialog() {
        $("#modalDiscount").modal('show');       
    }

    $("#discount_user_type_id").select2({
        ajax: {
            url: ajaxDiscountUserTypeSearchURL,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    id:  function() { return $("#dis_flow_id").val() },
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
        placeholder: 'Search for a user type',
        dropdownParent: $("#modalDiscount")
    }).on('change', function(e) {
        if (e.currentTarget.value !== "") {
            $("#discount_user_id").empty().trigger('change');
        }
    });

    $("#discount_user_id").select2({
        ajax: {
            url: ajaxDiscountUserSearchURL,
            dataType: 'json',
            delay: 0,
            data: function(params) {
                return {
                    id:  function() { return $("#dis_flow_id").val() },
                    user_type_id:  function() { return $("#discount_user_type_id").val() },
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
        dropdownParent: $("#modalDiscount")
    });

    function resetInputForm() {
        $("#no_of_source").val(1);
        $('#formDiscount').trigger("reset");
        $("#dis_flow_id").val(0);
        $("#discount_user_type_id").empty().trigger('change');
        $("#advanceFilterRows").empty().trigger('change');
        $("#discount_user_id").empty().trigger('change');
        $("#discount_name").empty().trigger('change');
        $("#discount_default_dis").empty().trigger('change');
        $("#user_default_dis").empty().trigger('change');
        $("#discount_dis").empty().trigger('change');
        $("#discount_status").select2("val", "1");
    }

    function editView(id) {
        resetInputForm();
        source_type_arr = [];
        $("#modalDiscount").modal('show');
        $("#modalDiscountLable").html("Edit Item Price #" + id);
        $("#formDiscount").hide();
        $(".loadingcls").show();

        $.ajax({
            type: 'GET',
            url: ajaxDiscountDetailURL + "?id=" + id,
            success: function(resultData) {
                if (resultData['status'] == 1) {
                    var itemData = resultData['data'].find(item => item.dis_flow_id == id); // Find the item with the clicked ID

                    if (itemData) {
                        $("#id").val(itemData['id']);
                        $("#dis_flow_id").val(itemData['dis_flow_id']);
                        $("#discount_name").val(itemData['items'][0]['name']);
                        $("#discount_default_dis").val(itemData['items'][0]['default_discount']);
                        $("#user_default_dis").val(itemData['items'][0]['user_discount']);
                        $("#discount_dis").val(itemData['items'][0]['discount']);
                        $("#discount_status").val(itemData['items'][0]['status']).trigger('change');

                        if (itemData['items'] !== undefined && itemData['items'] !== null && itemData['items'].length > 0) {
                            // Clear existing options in select elements
                            $("select[id^='discount_user_id']").empty().trigger('change');
                            $("#discount_user_type_id").empty().trigger('change');

                            for (var i = 0; i < itemData['items'].length; i++) {
                                var user_ids = itemData['items'][i]['user_id'];
                                var usertype = itemData['items'][i]['usertype'];
                                var discountData = itemData['items'][i]['discount'];

                                // Append user_id options
                                if (Array.isArray(user_ids) && user_ids.length > 0) {
                                    var selectId = i == 0 ? '#discount_user_id' : '#discount_user_id' + (i + 1);
                                    for (var j = 0; j < user_ids.length; j++) {
                                        var newOption = new Option(user_ids[j]['text'], user_ids[j]['id'], false, false);
                                        $(selectId).append(newOption);
                                    }
                                }

                                // Append usertype option
                                if (usertype !== null && usertype !== undefined) {
                                    var newOption = new Option(usertype['name'], usertype['id'], false, false);
                                    $('#discount_user_type_id').append(newOption);
                                }

                                // Process discount data as needed
                                if (discountData !== null && discountData !== undefined) {
                                    // Here, you can store the discount data as needed
                                    // For example, you can store it in an array or object
                                    // You can then use this data to populate discount fields or perform other actions
                                }
                            }

                            // If there are advance filter rows, populate their data
                            if (itemData['items'].length > 1) {
                                clearAllFilter(0); // Clear existing filters

                                for (var i = 1; i < itemData['items'].length; i++) {
                                    var user_type = itemData['items'][i]['usertype'];
                                    var user_ids = itemData['items'][i]['user_id'];
                                    var discountData = itemData['items'][i]['discount'];

                                    // Pass user_ids array along with other parameters to addNLoadfilter function
                                    addNLoadfilter(1, itemData['items'][i], user_type, user_ids, discountData);
                                }
                            }

                            // Set selected values for user_id select elements
                            var selectedSalePersons = [];
                            for (var i = 0; i < itemData['items'].length; i++) {
                                var user_ids = itemData['items'][i]['user_id'];
                                if (Array.isArray(user_ids) && user_ids.length > 0) {
                                    for (var j = 0; j < user_ids.length; j++) {
                                        selectedSalePersons.push(user_ids[j]['id']);
                                    }
                                }
                            }
                            $("select[id^='discount_user_id']").val(selectedSalePersons).trigger('change');
                        }

                        $(".loadingcls").hide();
                        $("#formDiscount").show();
                    } else {
                        toastr["error"]("Item not found for the clicked ID");
                    }
                } else {
                    toastr["error"](resultData['msg']);
                }
            }
        });
    }


    function deleteWarning(id) {
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: !0,
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "No, cancel!",
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
                        url: ajaxDiscountDeleteURL + "?id=" + id,
                        success: function(resultData) {
                            if (resultData['status'] == 1) {

                                reloadTable();
                                t()
                            }
                        }
                    });
                })
            },
        }).then(function(t) {
            if (t.value === true) {
                Swal.fire({
                    title: "Deleted!",
                    text: "Your record has been deleted.",
                    icon: "success"
                });
            }
        });
    }
</script>
@endsection