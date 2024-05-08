@extends('layouts.main')
@section('title', $data['title'])

@section('content')

    <style type="text/css">
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

    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Quotation Master</h4>
                    </div>
                </div>
            </div>

            <!-- end page title -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="q_quot_status_list" class="form-label">Status </label>
                                    <select id="q_quot_status_list" name="q_quot_status_list"
                                        class="form-control select2-apply">
                                        <option value="1">New Request</option>
                                        <option value="2">Change Request</option>
                                        <option value="3">Confirm Quotation</option>
                                        <option value="4">Rejected Quotation</option>
                                        <option value="0">Runing</option>
                                        <option value="ALL">All</option>
                                    </select>
                                </div>
                            </div>

                            <table id="datatable" class="table table-striped dt-responsive  nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Quot.No</th>
                                        <th>Party Name</th>
                                        <th>Provision</th>
                                        <th>Entry By</th>
                                        <th>Status</th>
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
    </div>

    @csrf
    @include('../quotation/master/quotation/comman/modal_quotation')
    @endsection('content')

@section('custom-scripts')


    <script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
    <script src="{{ asset('assets/libs/kendo/kendo.all.min.js') }}"></script>
    <script type="text/javascript">
        var ajaxPriceMasterDataURL = '{{ route('quot.master.ajax') }}';
        var ajaxOrderDetail = '{{ route('order.detail') }}';
        var ajaxItemWisePrintDownload = '{{ route('quot.master.itemwiseprint.download') }}';

        var PrintDownloadUrl = "{{ route('quot.master.itemwiseprint.download') }}";
        var csrfToken = $("[name=_token").val();

        var mainMasterPageLength = getCookie('mainMasterPageLength') !== undefined ? getCookie('mainMasterPageLength') : 10;
        var table = $('#datatable').DataTable({
            "aoColumnDefs": [{
                "bSortable": false,
                "aTargets": [5, 6]
            }],
            "order": [
                [0, 'desc']
            ],
            "processing": true,
            "serverSide": true,
            "pagingType": "full_numbers",
            "pageLength": mainMasterPageLength,
            "ajax": {
                "url": ajaxPriceMasterDataURL,
                "type": "POST",
                "data": {
                    "_token": csrfToken,
                    "status": function() {
                        return $("#q_quot_status_list").val()
                    },
                }
            },
            "aoColumns": [{
                    "mData": "id"
                },
                {
                    "mData": "quot_no"
                },
                {
                    "mData": "partyname"
                },
                {
                    "mData": "noofprovision"
                },
                {
                    "mData": "entryby"
                },
                {
                    "mData": "status"
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
        }






        $('#q_quot_status_list').on('change', function() {
            // alert(this.value);
            reloadTable();
        });


        $('#datatable').on('length.dt', function(e, settings, len) {
            setCookie('mainMasterPageLength', len, 100);
        });


        // $('#pdfpreviewshow').on('click', function() {
        //     $("#filtermodal").modal('hide');
        //     $("#modalItemWisePrint").modal('show');     
        // })

        $('#itempdfdownload').on('click', function() {
            var quot_id = $('#Quot_id').val();
            var quotgroup_id = $('#Quotgroup_id').val();
            var area_page_visible = $('#areatitle').prop("checked") ? 1 : 0;
            var area_summary_visible = $('#area').prop("checked") ? 1 : 0;
            var product_summary_visible = $('#product').prop("checked") ? 1 : 0;
            var product_summary_visible = $('#product').prop("checked") ? 1 : 0;

            var area_detailed_summary_visible = $('#areadetailtitle').prop("checked") ? 1 : 0;
            var area_detailed_gst_visible = $('#areagst').prop("checked") ? 1 : 0;
            var area_detailed_discount_visible = $('#areadiscount').prop("checked") ? 1 : 0;
            var area_detailed_rate_total_visible = $('#arearate').prop("checked") ? 1 : 0;

            var product_detailed_summary_visible = $('#producttitle').prop("checked") ? 1 : 0;
            var product_detailed_gst_visible = $('#productgst').prop("checked") ? 1 : 0;
            var product_detailed_discount_visible = $('#productdiscount').prop("checked") ? 1 : 0;
            var product_detailed_rate_total_visible = $('#productrate').prop("checked") ? 1 : 0;

            var wlt_and_others_detailed_summary_visible = $('#whiteliontitle').prop("checked") ? 1 : 0;
            var wlt_and_others_detailed_gst_visible = $('#wltandotherproductgst').prop("checked") ? 1 : 0;
            var wlt_and_others_detailed_discount_visible = $('#wltandotherdiscount').prop("checked") ? 1 : 0;
            var wlt_and_others_detailed_rate_total_visible = $('#wltandothernet').prop("checked") ? 1 : 0;

            let arry = [];
            arry.push({
                area_page_visible: area_page_visible,
                area_summary_visible: area_summary_visible,
                product_summary_visible: product_summary_visible,
                area_detailed_summary_visible: area_detailed_summary_visible,
                area_detailed_gst_visible: area_detailed_gst_visible,
                area_detailed_discount_visible: area_detailed_discount_visible,
                area_detailed_rate_total_visible: area_detailed_rate_total_visible,
                product_detailed_summary_visible: product_detailed_summary_visible,
                product_detailed_gst_visible: product_detailed_gst_visible,
                product_detailed_discount_visible: product_detailed_discount_visible,
                product_detailed_rate_total_visible: product_detailed_rate_total_visible,
                wlt_and_others_detailed_summary_visible: wlt_and_others_detailed_summary_visible,
                wlt_and_others_detailed_gst_visible: wlt_and_others_detailed_gst_visible,
                wlt_and_others_detailed_discount_visible: wlt_and_others_detailed_discount_visible,
                wlt_and_others_detailed_rate_total_visible: wlt_and_others_detailed_rate_total_visible,
            });

            var arr = JSON.stringify(arry);

            $('#itempdfdownload').attr('href', PrintDownloadUrl + '?quot_id=' + quot_id + '&quotgroup_id=' +
                quotgroup_id + '&visible_array=' + arr + '');

        })




        // $.ajax({
        //     type: 'GET',
        //     url: PrintDownloadUrl,
        //     data: {
        //         "quot_id": quot_id,
        //         "quotgroup_id": quotgroup_id,
        //         "area_page_visible":area_page_visible
        //     },
        //     success: function(resultData) {

        //     }
        // })


        // // NEW UPDATE START 
        function ItemWisePrint(id, quotgroup_id) {

            $('#Quot_id').val(id);
            $('#Quotgroup_id').val(quotgroup_id);
            // $('#itempdfdownload').attr('href', PrintDownloadUrl + '?quot_id=' + id + '&quotgroup_id=' + quotgroup_id + '&area_page_visible='+ area_page_visible +'');
            $("#filtermodal").modal('show');
            $('#areatitle').prop('checked', false);
            $('#area').prop('checked', false);
            $('#product').prop('checked', false);
            $('#areadiscount').prop('checked', false);
            $('#areagst').prop('checked', false);
            $('#arearate').prop('checked', false);
            $('#areadetailtitle').prop('checked', false);
            $('#arearate').prop('checked', false);
            $('#productrate').prop('checked', false);
            $('#productdiscount').prop('checked', false);
            $('#productgst').prop('checked', false);
            $('#producttitle').prop('checked', false);
            $('#whiteliontitle').prop('checked', false);
            $('#wltandotherproductgst').prop('checked', false);
            $('#wltandotherdiscount').prop('checked', false);
            $('#wltandothernet').prop('checked', false);
            $('#wltandothernet').prop('checked', false);
            $('.wltandotherdetailsummary').fadeOut(300);
            $('.productdetailsummary').fadeOut(300);
            $('.areadetailsummary').fadeOut(300);
            $('.areasummary').fadeOut(300);
            //     $('#roomandproductsubfilter').hide();
            //     $('.a5').prop('checked', false);
            //     $('#roomsubfilter').hide();
            //     $('.a6').prop('checked', false);
            //     $('#wltandotherssubfilter').hide();
            //     $('.a7').prop('checked', false);
            $('.close').on('click', function() {
                $("#filtermodal").modal('hide');
            })
            //     // $("#modalItemWisePrint").modal('show');
            //     $(".itemwise_print_loader").show();
            //     $(".itemwise_print_download").hide();

            // $('#itempdfdownload').attr('href', PrintDownloadUrl + '?quot_id=' + id + '&quotgroup_id=' + quotgroup_id + '');

        }
        // //NEW UPDATE END 


        $('input:checkbox.allgst').change(function() {
            $('.allnetamount').prop('checked', false);
            if ($(this).prop('checked')) {
                $('.allgst').prop('checked', true);
            } else {
                $('.allgst').prop('checked', false);
            }
        });

        $('input:checkbox.alldiscount').change(function() {
            $('.allnetamount').prop('checked', false);
            if ($(this).prop("checked")) {
                $('.alldiscount').prop('checked', true);
            } else {
                $('.alldiscount').prop('checked', false);
            }
        });

        $('input:checkbox.allnetamount').change(function() {
            $('.allgst').prop('checked', false);
            $('.alldiscount').prop('checked', false);
            if ($(this).prop('checked')) {
                $('.allnetamount').prop('checked', true);
            } else {
                $('.allnetamount').prop('checked', false);
            }
        });

        $("input:checkbox.areasummarytitle").click(function() {
            if ($(this).is(":checked")) {
                $('.areasummary').fadeIn(300);
            } else {
                $('.areasummary').fadeOut(300);
                $('#area').prop('checked', false);
                $('#product').prop('checked', false);
            }
        });
        $("input:checkbox.areadetailtitle").click(function() {
            if ($(this).is(":checked")) {
                $('.areadetailsummary').fadeIn(300);
            } else {
                $('.areadetailsummary').fadeOut(300);
                // $('#areagst').prop('checked', false);
                // $('#areadiscount').prop('checked', false);
                // $('#arearate').prop('checked', false);
            }
        });
        $("input:checkbox.producttitle").click(function() {
            if ($(this).is(":checked")) {
                $('.productdetailsummary').fadeIn(300);
            } else {
                $('.productdetailsummary').fadeOut(300);
                // $('#productgst').prop('checked', false);
                // $('#productdiscount').prop('checked', false);
                // $('#productrate').prop('checked', false);
            }
        });
        $("input:checkbox.whiteliontitle").click(function() {
            if ($(this).is(":checked")) {
                $('.wltandotherdetailsummary').fadeIn(300);
            } else {
                $('.wltandotherdetailsummary').fadeOut(300);
                // $('#wltandotherproductgst').prop('checked', false);
                // $('#wltandotherdiscount').prop('checked', false);
                // $('#wltandothernet').prop('checked', false);
            }
        });
    </script>
@endsection
