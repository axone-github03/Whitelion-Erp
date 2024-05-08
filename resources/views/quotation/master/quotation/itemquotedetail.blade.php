@extends('layouts.main')
@section('title', $data['title'])
@section('content')


    <style type="text/css">
        td p {
            max-width: 100%;
            white-space: break-spaces;
            word-break: break-all;
        }

        .badge-soft-running {
            color: #5e5e5e;
            background-color: #ffeb007a;
        }

        .badge-soft-change-request {
            color: #ff0000;
            background-color: #ff00001f;
        }

        .badge-soft-confirm {
            color: #ffffff;
            background-color: #418107;
        }

        .badge-soft-sent-quotation {
            color: #ffffff;
            background-color: #ff7c7c;
        }
    </style>

    <style>
        .switch {
            margin-top: 15px;
            position: relative;
            display: inline-block;
            width: 34px;
            height: 17px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ff000047;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 15px;
            width: 15px;
            left: 1px;
            bottom: 1px;
            background-color: #ffffff;
            -webkit-transition: .4s;
            transition: .4s;
        }


        input:checked+.slider {
            background-color: #07cd1266;
        }

        input:focus+.slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked+.slider:before {
            -webkit-transform: translateX(17px);
            -ms-transform: translateX(17px);
            transform: translateX(17px);
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }
    </style>

    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Quotation Item Detail Master</h4>
                        <input type="hidden" name="quotation_id" id="quotation_id" value="<?php echo $_GET['quotno']; ?>">
                        <input type="hidden" name="quotation_group_id" id="quotation_group_id" value="<?php
                        
                        use App\Models\Wltrn_Quotation;
                        
                        echo Wltrn_Quotation::find($_GET['quotno'])->quotgroup_id; ?>">
                        <input type="hidden" name="quotation_range" id="quotation_range" value="<?php echo Wltrn_Quotation::find($_GET['quotno'])->default_range; ?>">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body p-2">
                            <div class="d-flex" style="width: fit-content;">
                                <div class="d-flex flex-column ms-2 pe-2" style="border-right: solid #dbdbdb 1px;">
                                    <div class="text-capitalize"><span style="font-weight: bold;">Quote no : </span> <span
                                            id="su_quot_no">-</span> </div>
                                    <div class="text-capitalize"><span style="font-weight: bold;">Quote date : </span> <span
                                            id="su_quot_date">-</span> </div>
                                    <div class="text-capitalize"><span style="font-weight: bold;">Quote Type : </span> <span
                                            id="su_quot_type">-</span> </div>
                                    <div class="text-capitalize"><span style="font-weight: bold;">Quote version : </span>
                                        <span id="su_quot_version">-</span>
                                    </div>
                                </div>
                                <div class="d-flex flex-column ms-2 pe-2" style="border-right: solid #dbdbdb 1px;">
                                    <div class="text-capitalize"><span style="font-weight: bold;">Site Name : </span> <span
                                            id="su_quot_site_name">-</span> </div>
                                    <div class="text-capitalize"><span style="font-weight: bold;">Site Add. : </span> <span
                                            id="su_quot_site_address">-</span> </div>
                                    <div class="text-capitalize"><span style="font-weight: bold;">Customer Name : </span>
                                        <span id="su_quot_cust_name">-</span>
                                    </div>
                                    <div class="text-capitalize"><span style="font-weight: bold;">Customer Mobile : </span>
                                        <span id="su_quot_cust_mobile">-</span>
                                    </div>
                                </div>
                                <div class="d-flex flex-column ms-2 pe-2" style="border-right: solid #dbdbdb 1px;">
                                    <div class="text-capitalize"><span style="font-weight: bold;">Plate : </span> <span
                                            id="su_range_plate">-</span> </div>
                                    <div class="text-capitalize"><span style="font-weight: bold;">Accessories : </span>
                                        <span id="su_range_accessories">-</span>
                                    </div>
                                    <div class="text-capitalize"><span style="font-weight: bold;">Whitelion : </span> <span
                                            id="su_range_whitelion">-</span> </div>
                                    <div class="text-capitalize"><span style="font-weight: bold;">Current Status : </span>
                                        <span id="su_quot_Status">-</span>
                                    </div>
                                </div>
                                <div class="d-flex flex-column ms-2 pe-2">
                                    <div class="text-capitalize"><span style="font-weight: bold;">Gross Amount(₹) : </span>
                                        <span id="su_quote_total_gross_amt">-</span>
                                    </div>
                                    <div class="text-capitalize"><span style="font-weight: bold;">CGST(₹) : </span> <span
                                            id="su_quote_total_cgst_amt">-</span> </div>
                                    <div class="text-capitalize"><span style="font-weight: bold;">SGST(₹) : </span> <span
                                            id="su_quote_total_sgst_amt">-</span> </div>
                                    <div class="text-capitalize"><span style="font-weight: bold;">Final Amount(₹) : </span>
                                        <span id="su_quote_total_net_amt">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- end page title -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-end align-items-center">
                                <label class="form-label" style="margin-right: 1rem;">Status : </label>
                                <label for="q_quotation_status" class="form-label" style="margin-right: 3rem;">
                                    <select id="q_quotation_status" name="q_quotation_status" class="form-control">

                                        <option value="0" <?php echo Wltrn_Quotation::find($_GET['quotno'])->status == '0' ? 'selected' : ''; ?>>Running</option>
                                        <option value="1" <?php echo Wltrn_Quotation::find($_GET['quotno'])->status == '1' ? 'selected' : ''; ?>>New Request</option>
                                        <option value="2" <?php echo Wltrn_Quotation::find($_GET['quotno'])->status == '2' ? 'selected' : ''; ?>>Change Request</option>
                                        <option value="3" <?php echo Wltrn_Quotation::find($_GET['quotno'])->status == '3' ? 'selected' : ''; ?>>Confirm Quotation</option>
                                        <option value="4" <?php echo Wltrn_Quotation::find($_GET['quotno'])->status == '4' ? 'selected' : ''; ?>>Rejected Quotation</option>
                                    </select>
                                </label>
                                <label class="form-label d-none" style="margin-right: 3rem;">
                                    <a onclick="filter_model()" href="javascript: void(0);" title="Item Wise Print">
                                        <i class="bx bx-filter-alt" style="margin-right: 5px;"></i>
                                        Filter
                                    </a>
                                </label>
                                <label class="form-label" style="margin-right: 3rem;">
                                    <a onclick="add_discount_model(<?php echo $_GET['quotno']; ?>)" href="javascript: void(0);"
                                        title="Item Wise Print">
                                        <i class="bx bxs-discount" style="margin-right: 5px;"></i>
                                        Apply Discount
                                    </a>
                                </label>
                                <label class="form-label d-none" style="margin-right: 3rem;">
                                    <a onclick="add_dummy_model()" href="javascript: void(0);" title="Item Wise Print">
                                        <i class="bx bx-sort" style="margin-right: 5px;"></i>
                                        Add Dummy
                                    </a>
                                </label>
                                <label class="form-label" style="margin-right: 3rem;">
                                    <a onclick="changerange()" href="javascript: void(0);" title="Item Wise Print">
                                        <i class="bx bx-sort" style="margin-right: 5px;"></i>
                                        Change Range
                                    </a>
                                </label>
                                <label class="form-label " style="margin-right: 3rem;">
                                    <a href="{{ route('quotation.convertation') }}?quotno=<?php echo $_GET['quotno']; ?>&quotgroup_id=<?php echo Wltrn_Quotation::find($_GET['quotno'])->quotgroup_id ?>">Posh To Quartz</a>
                                </label>
                                <label class="form-label d-none" style="margin-right: 3rem;">
                                    <a onclick="resolve_issue_model()" href="javascript: void(0);"
                                        title="Item Wise Print">
                                        <i class="bx bx-bug" style="margin-right: 5px;"></i>
                                        Resolve
                                    </a>
                                </label>
                            </div>
                            <table id="datatable" class="table table-striped dt-responsive nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Room</br>Name</th>
                                        <th>Item Name</th>
                                        <th>Brand</th>
                                        <th class="text-center">Remain</br>Module</th>
                                        <th class="text-center">MRP</th>
                                        <th class="text-center">Dicount</br>/ GST</th>
                                        <!-- <th class="text-center">Gross</th> -->
                                        <!-- <th class="text-center">GST</th> -->
                                        <th class="text-center">Final</th>
                                        <th class="text-center">Action</th>
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
    </div>

    @csrf
    @include('../quotation/master/quotation/comman/modal_quotation')
@endsection('content')
@section('custom-scripts')

    <script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>

    <script type="text/javascript">
        var ajaxPriceMasterDataURL = '{{ route('quot.itemquotedetail.data') }}';
        var ajaxURLSearchPlateSubGroup = '{{ route('quot.search.plate.data') }}';
        var ajaxURLSearchAccessoriesSubGroup = '{{ route('quot.search.accessories.data') }}';
        var ajaxURLSearchWhitelionSubGroup = '{{ route('quot.search.whitelion.data') }}';
        var ajaxURLSearchItem = '{{ route('quot.search.item.data') }}';
        var ajaxURLSearchItemBrand = '{{ route('quot.search.item.brand.data') }}';
        var ajaxURLSearchBoardItem = '{{ route('quot.search.boarditem.ajax') }}';
        var ajaxURLQuotationSummary = '{{ route('quot.summary.data') }}';
        var ajaxURLBoardAddons = '{{ route('quot.search.board.addons') }}';
        var ajaxURLGetItemPrice = '{{ route('quot.get.item.price') }}';
        var ajaxURLSaveBoardAddon = '{{ route('quot.save.board.addon') }}';

        var csrfToken = $("[name=_token").val();

        var mainMasterPageLength = getCookie('mainMasterPageLength') !== undefined ? getCookie('mainMasterPageLength') : 10;
        var table = $('#datatable').DataTable({
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": [0, 8]
            }],
            "order": [
                [0, 'desc']
            ],
            "responsive": true,
            "processing": true,
            "serverSide": true,
            "pagingType": "full_numbers",
            "pageLength": mainMasterPageLength,
            "ajax": {
                "url": ajaxPriceMasterDataURL,
                "type": "POST",
                "data": {
                    "_token": csrfToken,
                    "quotno": <?php echo $_GET['quotno']; ?>,
                }
            },
            "aoColumns": [{
                    "mData": "id"
                },
                {
                    "mData": "room_name"
                },
                {
                    "mData": "itemname"
                },
                {
                    "mData": "brand"
                },
                {
                    "mData": "remain_module"
                },
                {
                    "mData": "mrp"
                },
                {
                    "mData": "dicount"
                },
                {
                    "mData": "final"
                },
                {
                    "mData": "action"
                }
            ],
            "drawCallback": function() {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                })

            }
        });

        function reloadTable() {
            table.ajax.reload(null, false);
            full_quotation_summary();
        }

        $('#datatable').on('length.dt', function(e, settings, len) {
            setCookie('mainMasterPageLength', len, 100);
        });

        $(document).ready(function() {
            $('#q_quotation_status').on('change', function() {
                var ajaxURLChangeQuotationStatus = '{{ route('quot.status.change.ajax') }}';
                $.ajax({
                    type: 'GET',
                    url: ajaxURLChangeQuotationStatus,
                    data: {
                        "quot_id": $('#quotation_id').val(),
                        "status": this.value
                    },
                    success: function(resultData) {
                        if (resultData['status'] == 1) {
                            toastr["success"](resultData['msg']);
                            reloadTable();
                        } else {
                            toastr["error"](resultData['msg']);
                        }

                    }
                });
            });
            full_quotation_summary();
        });

        function full_quotation_summary() {
            $.ajax({
                type: 'GET',
                url: ajaxURLQuotationSummary,
                data: {
                    "room_no": $('#quotation_id').val(),
                    "board_no": $('#quotation_id').val(),
                    "quot_id": $('#quotation_id').val(),
                },
                success: function(resultData) {
                    if (resultData['status'] == 1) {
                        // toastr["success"](resultData['msg']); 
                        var quotation_detail_summary = resultData['quotation_detail_summary'];
                        var quotation_amount_summary = resultData['quotation_amount_summary'];
                        $('#su_quot_no').text('Q' + quotation_detail_summary['quotno']);
                        $('#su_quot_date').text(quotation_detail_summary['quot_date']);
                        $('#su_quot_type').text(quotation_detail_summary['type_name']);
                        $('#su_quot_version').text(quotation_detail_summary['quot_no_str']);
                        $('#su_quot_site_name').text(quotation_detail_summary['site_name']);
                        $('#su_quot_site_address').text(quotation_detail_summary['siteaddress']);
                        $('#su_quot_cust_name').text(quotation_detail_summary['customer_name']);
                        $('#su_quot_cust_mobile').text(quotation_detail_summary['customer_contact_no']);

                        $('#su_range_plate').text(resultData['quot_range_plate']);
                        $('#su_range_accessories').text(resultData['quot_range_acc']);
                        $('#su_range_whitelion').text(resultData['quot_range_whitelion']);
                        $('#su_quot_Status').html(resultData['quot_status']);

                        // $('#su_quote_total_gross_amt').text(quotation_amount_summary['gross_amount']);
                        // $('#su_quote_total_cgst_amt').text(quotation_amount_summary['cgst_amount']);
                        // $('#su_quote_total_sgst_amt').text(quotation_amount_summary['sgst_amount']);
                        // $('#su_quote_total_net_amt').text(quotation_amount_summary['net_amount']);

                        $('#su_quote_total_gross_amt').text(Math.round(quotation_amount_summary['gross_amount'])
                            .toFixed(2));
                        $('#su_quote_total_cgst_amt').text(Math.round(quotation_amount_summary['cgst_amount'])
                            .toFixed(2));
                        $('#su_quote_total_sgst_amt').text(Math.round(quotation_amount_summary['sgst_amount'])
                            .toFixed(2));
                        $('#su_quote_total_net_amt').text(Math.round(quotation_amount_summary['net_amount'])
                            .toFixed(2));

                    } else {
                        toastr["error"]('Please Refresh Page');
                    }

                }
            });
        }



        // function pdfroom(id) {
        //     // modalquotation
        //     $("#modalquotationprint").modal('show');
        // }

        function itemwise(id) {
            // modalquotation
            $("#modalitemwiseprint").modal('show');
        }

        function roomwise(id) {
            // modalquotation
            $("#modalroomwiseprint").modal('show');
        }

        // ---------- QUOTATION RANGE SELECTOR ** START ** ----------
        $('#q_subgroup_plte_id').change(function($e) {
            // alert($e);
            $("#q_subgroup_access_id").empty().trigger('change');
            $("#q_subgroup_whitelion_id").empty().trigger('change');
        });

        $("#q_subgroup_plte_id").select2({
            ajax: {
                url: ajaxURLSearchPlateSubGroup,
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
            placeholder: 'Select Plate SubGroup',
            dropdownParent: $("#modalChangeRange"),

        });

        $("#q_subgroup_access_id").select2({
            ajax: {
                url: ajaxURLSearchAccessoriesSubGroup,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        "plate_subgroup": function() {
                            return $("#q_subgroup_plte_id").val()
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
            placeholder: 'Select Item SubGroup',
            dropdownParent: $("#modalChangeRange"),
        });

        $("#q_subgroup_whitelion_id").select2({
            ajax: {
                url: ajaxURLSearchWhitelionSubGroup,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        "plate_subgroup": function() {
                            return $("#q_subgroup_plte_id").val()
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
            placeholder: 'Select Item SubGroup',
            dropdownParent: $("#modalChangeRange"),
        });

        // ---------- QUOTATION RANGE SELECTOR ** START ** ----------

        // ---------- BOARD RANGE SELECTOR ** START ** ----------
        $('#q_board_subgroup_plte_id').change(function($e) {
            // alert($e);
            $("#q_board_subgroup_access_id").empty().trigger('change');
            $("#q_board_subgroup_whitelion_id").empty().trigger('change');
        });

        $("#q_board_subgroup_plte_id").select2({
            ajax: {
                url: ajaxURLSearchPlateSubGroup,
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
            placeholder: 'Select Plate SubGroup',
            dropdownParent: $("#modalQuotBoardDetail"),

        });

        $("#q_board_subgroup_access_id").select2({
            ajax: {
                url: ajaxURLSearchAccessoriesSubGroup,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        "plate_subgroup": function() {
                            return $("#q_board_subgroup_plte_id").val()
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
            placeholder: 'Select Item SubGroup',
            dropdownParent: $("#modalQuotBoardDetail"),
        });

        $("#q_board_subgroup_whitelion_id").select2({
            ajax: {
                url: ajaxURLSearchWhitelionSubGroup,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        "plate_subgroup": function() {
                            return $("#q_board_subgroup_plte_id").val()
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
            placeholder: 'Select Item SubGroup',
            dropdownParent: $("#modalQuotBoardDetail"),
        });
        // ---------- BOARD RANGE SELECTOR ** END ** ----------



        function quotation_board_status(val, quot_rommno, quot_boardno,type) {
            var ajaxURLQuotationBoardStaus = '{{ route('quot.doard.status.data') }}';
            var board_status = val.checked ? 1 : 0;

            $.ajax({
                type: 'GET',
                url: ajaxURLQuotationBoardStaus,
                data: {
                    "quot_id": $('#quotation_id').val(),
                    "quotgroup_id": $('#quotation_group_id').val(),
                    "room_no": quot_rommno,
                    "board_no": quot_boardno,
                    "status": board_status,
                    "type": "BOARD",
                },

                success: function(resultData) {
                    if (resultData['status'] == 1) {
                        toastr["success"](resultData['msg'] + ' ✅');
                        reloadTable();
                    } else {
                        toastr["error"](resultData['msg']);
                    }
                }
            });
        }
        
        function quotation_room_status(val, quot_rommno, quot_boardno,type) {
            var ajaxURLQuotationBoardStaus = '{{ route('quot.doard.status.data') }}';
            var board_status = val.checked ? 1 : 0;

            $.ajax({
                type: 'GET',
                url: ajaxURLQuotationBoardStaus,
                data: {
                    "quot_id": $('#quotation_id').val(),
                    "quotgroup_id": $('#quotation_group_id').val(),
                    "room_no": quot_rommno,
                    "board_no": quot_boardno,
                    "status": board_status,
                    "type": "ROOM",
                },

                success: function(resultData) {
                    if (resultData['status'] == 1) {
                        toastr["success"](resultData['msg'] + ' ✅');
                        reloadTable();
                    } else {
                        toastr["error"](resultData['msg']);
                    }
                }
            });
        }
    </script>
@endsection
