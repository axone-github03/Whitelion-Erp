<div class="modal fade" id="modalItemPriceExcelUpload" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalItemPriceExcelUploadLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalItemPriceExcelUploadLabel">Upload Price Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formItemPriceExcelUpdate" action="{{route('quot.itemprice.master.update.price.excel')}}" method="POST" class="needs-validation" novalidate>
                <div class="modal-body">

                    <div class="col-md-12">
                        <div class="mb-3 d-flex flex-column">
                            <label class="form-label inquiry-questions-lable" style="font-size: larger;font-weight: bold;">Instructions : </label>
                            <label class="form-label inquiry-questions-lable"> 1. Download the format file and fill it with proper data.</label>
                            <label class="form-label inquiry-questions-lable">2. You can download the example file to understand how the data must be filled.</label>
                            <label class="form-label inquiry-questions-lable">3. Once you have downloaded and filled the format file upload it in the form below and Save.</label>
                            <!-- <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="width: fit-content;">Download Sample Excel</button> -->
                            <a href="{{route('quot.itemprice.master.export')}}" target="_blank" class="btn btn-light" type="button"><i class="bx bx-export font-size-16 align-middle me-2"></i>Download Data Excel</a>
                        </div>
                    </div>

                    @csrf
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="q_price_excel" class="form-label inquiry-questions-lable" style="font-size: larger;font-weight: bold;">Upload Item Price Excel</label>
                            <input class="form-control" type="file" value="" id="q_price_excel" name="q_price_excel" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                        </div>
                    </div>

                    <div class="modal-footer">

                        <div id="btnSave">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button id="btnSaveFinal" type="submit" class="btn btn-primary UpdatePriceThrewExcel">Save</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade bd-example-modal-xl" id="modalItemPriceUpdate" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalItemPriceUpdateLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalItemPriceUpdateLabel">Item Price Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-8">
                            <div class="row">
                                <div class="col-6">
                                    <div class="ajax-select">
                                        <!-- <label for="u_price_company" class="form-label">Company </label> -->
                                        <select class="form-control float-start mb-2 select2-ajax" id="u_price_company" name="u_price_company">
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="ajax-select">
                                        <!-- <label for="u_price_group" class="form-label">Group </label> -->
                                        <select class="form-control float-start mb-2 select2-ajax" id="u_price_group" name="u_price_group">
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="ajax-select mt-2">
                                        <!-- <label for="u_price_subgroup" class="form-label">Brand </label> -->
                                        <select class="form-control float-start mb-2 select2-ajax" id="u_price_subgroup" name="u_price_subgroup">
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="ajax-select mt-2">
                                        <!-- <label for="u_price_item" class="form-label">Item </label> -->
                                        <select class="form-control float-start mb-2 select2-ajax" id="u_price_item" name="u_price_item">
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-2 align-self-center">
                            <div class="row">
                                <!-- <label for="u_item_filtered_price" class="form-label">Price </label> -->
                                <input type="number" step="1" class="form-control float-start" id="u_item_filtered_price" name="u_item_filtered_price" placeholder="Price" value="">
                            </div>
                        </div>
                        <div class="col-2 align-self-center">
                            <div class="row">
                                <div class="col-12">
                                    <button id="saveFilteredPrice" type="button" class="btn btn-primary waves-effect waves-light float-start applyAll">Apply All</button>
                                    <button id="clearFiltereddata" type="button" class="btn btn-light waves-effect waves-light float-end clearfilter">Clear</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <table id="itemPriceUpdateTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th style="width:5%">No.</th>
                                    <th style="width:30%">Item</br>Company</th>
                                    <th style="width:30%">Group</br>Subgroup</th>
                                    <th style="width:15%">Code</th>
                                    <th style="width:30%">MRP</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" id="saveManuallyPriceRow" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade bd-example-modal-xl" id="modalItemFlowUpdate" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalItemFlowUpdateLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalItemFlowUpdateLabel">Item Multiple Flow Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-8">
                            <div class="row">
                                <div class="col-6">
                                    <div class="ajax-select">
                                        <!-- <label for="u_flow_company" class="form-label">Company </label> -->
                                        <select class="form-control float-start mb-2 select2-ajax" id="u_flow_company" name="u_flow_company">
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="ajax-select">
                                        <!-- <label for="u_flow_group" class="form-label">Group </label> -->
                                        <select class="form-control float-start mb-2 select2-ajax" id="u_flow_group" name="u_flow_group">
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="ajax-select mt-2">
                                        <!-- <label for="u_flow_subgroup" class="form-label">Brand </label> -->
                                        <select class="form-control float-start mb-2 select2-ajax" id="u_flow_subgroup" name="u_flow_subgroup">
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="ajax-select mt-2">
                                        <!-- <label for="u_flow_item" class="form-label">Item </label> -->
                                        <select class="form-control float-start mb-2 select2-ajax" id="u_flow_item" name="u_flow_item">
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-2 align-self-center">
                                <div class="ajax-select mt-2">
                                    <!-- <label for="u_flow_subgroup" class="form-label">Brand </label> -->
                                    <select class="form-control float-start mb-2 select2-ajax select2-multiple" multiple="multiple" id="u_item_filtered_flow" name="u_item_filtered_flow[]">
                                    </select>
                                </div>
                        </div>
                        <div class="col-2 align-self-center">
                            <div class="row">
                                <div class="col-12">
                                    <button id="saveFilteredFlow" type="button" class="btn btn-primary waves-effect waves-light float-start applyAll">Apply All</button>
                                    <button id="clearFiltereddataflow" type="button" class="btn btn-light waves-effect waves-light float-end clearfilter">Clear</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <table id="itemFlowUpdateTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th style="width:5%">No.</th>
                                    <th style="width:30%">Item</br>Company</th>
                                    <th style="width:30%">Group</br>Subgroup</th>
                                    <th style="width:15%">Code</th>
                                    <th style="width:30%">Multi Flow</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" id="saveManuallyFlowRow" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(".UpdatePriceThrewExcel").click(function() {
        $(".UpdatePriceThrewExcel").html('<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span>Saving...</span>');
    });

    function showPriceUpdateDialog() {

        $("#modalItemPriceUpdate").modal('show');

        var ajaxItemPriceUpdateDataURL = '{{route("quot.itemprice.master.price.update.ajax")}}';

        itemPriceUpdateTable = $('#itemPriceUpdateTable').DataTable({
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": [0]
            }],
            "processing": true,
            "serverSide": true,
            "bDestroy": true,
            "pagingType": "full_numbers",
            "pageLength": 10,
            "ajax": {
                "url": ajaxItemPriceUpdateDataURL,
                "type": "POST",
                "data": {
                    "_token": csrfToken,
                    "filter_company": function() {
                        return $('#u_price_company').val()
                    },
                    "filter_group": function() {
                        return $('#u_price_group').val()
                    },
                    "filter_subgroup": function() {
                        return $('#u_price_subgroup').val()
                    },
                    "filter_item": function() {
                        return $('#u_price_item').val()
                    }
                }
            },
            "aoColumns": [{
                    "mData": "id"
                },
                {
                    "mData": "item"
                },
                {
                    "mData": "brand"
                },
                {
                    "mData": "code"
                },
                {
                    "mData": "mrp"
                }
            ]
        });


        $("#u_price_company").select2({
            ajax: {
                url: ajaxURLSearchCompany,
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
            placeholder: 'Select Company',
            dropdownParent: $("#modalItemPriceUpdate"),
        }).on('change', function(e) {
            $("#u_price_group").empty().trigger('change');
            $("#u_price_subgroup").empty().trigger('change');
            itemPriceUpdateTable.ajax.reload(null, false);
        });

        $("#u_price_group").select2({
            ajax: {
                url: ajaxURLSearchItemGroup,
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
            placeholder: 'Select Group',
            dropdownParent: $("#modalItemPriceUpdate"),
        }).on('change', function(e) {
            $("#u_price_subgroup").empty().trigger('change');
            itemPriceUpdateTable.ajax.reload(null, false);
        });

        $("#u_price_subgroup").select2({
            ajax: {
                url: ajaxURLSearchItemSubGroup,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        "group_id": function() {
                            return $('#u_price_group').val();
                        },
                        "company_id": function() {
                            return $("#u_price_company").val();
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
            placeholder: 'Select brand',
            dropdownParent: $("#modalItemPriceUpdate"),
        }).on('change', function(e) {
            itemPriceUpdateTable.ajax.reload(null, false);
        });

        $("#u_price_item").select2({
            ajax: {
                url: ajaxURLSearchItem,
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
            placeholder: 'Select Item',
            dropdownParent: $("#modalItemPriceUpdate"),
        }).on('change', function(e) {
            itemPriceUpdateTable.ajax.reload(null, false);
        });

        $('#clearFiltereddata').click(function() {
            $("#u_price_company").empty().trigger('change');
            $("#u_price_group").empty().trigger('change');
            $("#u_price_subgroup").empty().trigger('change');
            $("#u_price_item").empty().trigger('change');
            $("#u_item_filtered_price").val('');
            itemPriceUpdateTable.ajax.reload(null, false);
            $("#saveFilteredPrice").html("Apply All");
            $("#saveManuallyPriceRow").html("Save");
        });

        $('#saveFilteredPrice').click(function() {

            $("#saveFilteredPrice").html('<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span  >Appllying...</span>');
            var ajaxURLItemFilteredPriceSave = '{{route("quot.itemprice.master.filtered.price.save")}}';
            if ($('#u_item_filtered_price').val() == '') {
                toastr["error"]('Please Enter Price ⛔️');
                $("#saveFilteredPrice").html("Apply All");
            } else {
                $.ajax({
                    type: 'POST',
                    url: ajaxURLItemFilteredPriceSave,
                    data: {
                        "filter_company": ($('#u_price_company').val() == null) ? '0' : $('#u_price_company').val(),
                        "filter_group": ($('#u_price_group').val() == null) ? '0' : $('#u_price_group').val(),
                        "filter_subgroup": ($('#u_price_subgroup').val() == null) ? '0' : $('#u_price_subgroup').val(),
                        "filter_item": ($('#u_price_item').val() == null) ? '0' : $('#u_price_item').val(),
                        "price": $('#u_item_filtered_price').val(),
                        "_token": $("[name=_token").val(),
                    },
                    success: function(responseText) {

                        if (responseText['status'] == 1) {
                            $("#saveFilteredPrice").html("Apply All");
                            toastr["success"](responseText['msg']);
                            reloadTable();
                            itemPriceUpdateTable.ajax.reload(null, false);

                        } else {
                            $("#saveFilteredPrice").html("Apply All");
                            toastr["error"](responseText['msg']);
                        }

                    }
                });

            }

        });

        $('#saveManuallyPriceRow').click(function() {
            let list = [];
            let discount = $('#itemPriceUpdateTable input[name="input_price_text"]').each(function(ind) {
                let id = $(this).attr("id");
                let val = $(this).val();
                list.push({
                    val: val,
                    id: id
                });
            });

            $("#saveManuallyPriceRow").html('<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span>Saving...</span>');
            var ajaxURLItemPriceAllSave = '{{route("quot.itemprice.master.saveall.price.save")}}';

            $.ajax({
                type: 'POST',
                url: ajaxURLItemPriceAllSave,
                data: {
                    "price_list": list,
                    "_token": $("[name=_token").val(),
                },
                success: function(responseText) {

                    if (responseText['status'] == 1) {
                        $("#saveManuallyPriceRow").html("Save");
                        toastr["success"](responseText['msg']);
                        reloadTable();
                        itemPriceUpdateTable.ajax.reload(null, false);

                    } else {
                        $("#saveManuallyPriceRow").html("Save");
                        toastr["error"](responseText['msg']);
                    }

                }
            });
        });
    }

    function showFlowUpdateDialog() {

        $("#modalItemFlowUpdate").modal('show');

        var ajaxItemFlowUpdateDataURL = '{{route("quot.itemprice.master.flow.update.ajax")}}';

        itemFlowUpdateTable = $('#itemFlowUpdateTable').DataTable({
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": [0]
            }],
            "processing": true,
            "serverSide": true,
            "bDestroy": true,
            "pagingType": "full_numbers",
            "pageLength": 10,
            "ajax": {
                "url": ajaxItemFlowUpdateDataURL,
                "type": "POST",
                "data": {
                    "_token": csrfToken,
                    "filter_company": function() {
                        return $('#u_flow_company').val()
                    },
                    "filter_group": function() {
                        return $('#u_flow_group').val()
                    },
                    "filter_subgroup": function() {
                        return $('#u_flow_subgroup').val()
                    },
                    "filter_item": function() {
                        return $('#u_flow_item').val()
                    }
                }
            },
            "aoColumns": [{
                    "mData": "id"
                },
                {
                    "mData": "item"
                },
                {
                    "mData": "brand"
                },
                {
                    "mData": "code"
                },
                {
                    "mData": "flow_ids"
                }
            ]
        });

        $("#u_flow_company").select2({
            ajax: {
                url: ajaxURLSearchCompany,
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
            placeholder: 'Select Company',
            dropdownParent: $("#modalItemFlowUpdate"),
        }).on('change', function(e) {
            $("#u_flow_group").empty().trigger('change');
            $("#u_flow_subgroup").empty().trigger('change');
            itemFlowUpdateTable.ajax.reload(null, false);
        });

        $("#u_flow_group").select2({
            ajax: {
                url: ajaxURLSearchItemGroup,
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
            placeholder: 'Select Group',
            dropdownParent: $("#modalItemFlowUpdate"),
        }).on('change', function(e) {
            $("#u_flow_subgroup").empty().trigger('change');
            itemFlowUpdateTable.ajax.reload(null, false);
        });

        $("#u_flow_subgroup").select2({
            ajax: {
                url: ajaxURLSearchItemSubGroup,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        "group_id": function() {
                            return $('#u_flow_group').val();
                        },
                        "company_id": function() {
                            return $("#u_flow_company").val();
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
            placeholder: 'Select brand',
            dropdownParent: $("#modalItemFlowUpdate"),
        }).on('change', function(e) {
            itemFlowUpdateTable.ajax.reload(null, false);
        });

        $("#u_flow_item").select2({
            ajax: {
                url: ajaxURLSearchItem,
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
            placeholder: 'Select Item',
            dropdownParent: $("#modalItemFlowUpdate"),
        }).on('change', function(e) {
            itemFlowUpdateTable.ajax.reload(null, false);
        });

        $("#u_item_filtered_flow").select2({
            ajax: {
                url: ajaxURLSearchMultiFlow,
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
            placeholder: 'Search Multiple Flow',
            dropdownParent: $("#modalItemFlowUpdate"),
        }).on('change', function(e) {
            itemFlowUpdateTable.ajax.reload(null, false);
        });

        $('#clearFiltereddataflow').click(function() {
            $("#u_flow_company").empty().trigger('change');
            $("#u_flow_group").empty().trigger('change');
            $("#u_flow_subgroup").empty().trigger('change');
            $("#u_flow_item").empty().trigger('change');
            $("#u_item_filtered_flow").empty().trigger('change');
            itemFlowUpdateTable.ajax.reload(null, false);
            $("#saveFilteredFlow").html("Apply All");
            $("#saveManuallyFlowRow").html("Save");
        });

        $('#saveFilteredFlow').click(function() {

            $("#saveFilteredFlow").html('<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span  >Appllying...</span>');
            var ajaxURLItemFilteredFlowSave = '{{route("quot.itemprice.master.filtered.flow.save")}}';
            if ($('#u_item_filtered_flow').val() == '') {
                toastr["error"]('Please Enter Flow ⛔️');
                $("#saveFilteredFlow").html("Apply All");
            } else {
                $.ajax({
                    type: 'POST',
                    url: ajaxURLItemFilteredFlowSave,
                    data: {
                        "filter_company": ($('#u_flow_company').val() == null) ? '0' : $('#u_flow_company').val(),
                        "filter_group": ($('#u_flow_group').val() == null) ? '0' : $('#u_flow_group').val(),
                        "filter_subgroup": ($('#u_flow_subgroup').val() == null) ? '0' : $('#u_flow_subgroup').val(),
                        "filter_item": ($('#u_flow_item').val() == null) ? '0' : $('#u_flow_item').val(),
                        "flow_ids": $('#u_item_filtered_flow').val(),
                        
                        "_token": $("[name=_token").val(),
                    },
                    success: function(responseText) {

                        if (responseText['status'] == 1) {
                            $("#saveFilteredFlow").html("Apply All");
                            toastr["success"](responseText['msg']);
                            reloadTable();
                            itemFlowUpdateTable.ajax.reload(null, false);

                        } else {
                            $("#saveFilteredFlow").html("Apply All");
                            toastr["error"](responseText['msg']);
                        }

                    }
                });

            }

        });

        $('#saveManuallyFlowRow').click(function() {
            let list = [];
            // Select all rows of the table with the ID itemFlowUpdateTable
            $('#itemFlowUpdateTable .ajax-select select').each(function() {
                let id = $(this).attr("id");
                let val = $(this).val(); // Assuming the select element is inside a container with the class ajax-select
                list.push({
                    val: val,
                    id: id
                });
            });

            $("#saveManuallyFlowRow").html('<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span>Saving...</span>');
            var ajaxURLItemFlowAllSave = '{{ route("quot.itemprice.master.saveall.flow.save") }}';

            $.ajax({
                type: 'POST',
                url: ajaxURLItemFlowAllSave,
                data: {
                    "flow_list": list,
                    "_token": $("[name=_token").val(),
                },
                success: function(responseText) {
                    if (responseText['status'] == 1) {
                        $("#saveManuallyFlowRow").html("Save");
                        toastr["success"](responseText['msg']);
                        reloadTable();
                        itemFlowUpdateTable.ajax.reload(null, false);
                    } else {
                        $("#saveManuallyFlowRow").html("Save");
                        toastr["error"](responseText['msg']);
                    }
                }
            });
        });
    }
    
</script>