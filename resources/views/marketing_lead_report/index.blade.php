@extends('layouts.main')
@section('title', $data['title'])
@section('content')



    <link href="{{ asset('assets/libs/summernote/summernote.min.css') }}" rel="stylesheet">


    <link href="{{ asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet"
        type="text/css">

    <style type="text/css">
        .btn-t {
            padding: 0 5px !important;
        }

        .btn-m {
            padding: 0 3px !important;
        }

        #datatable_info {
            color: black;
            font-size: 14px;
            font-weight: 500;
        }

        #datatable_length {
            margin-top: 10px;
            float: left;
        }

        #datatable_paginate {
            margin-top: 10px;
        }

        .follow_type_id {
            font-size: 10px;
        }




        .note-hint-popover .popover-content .note-hint-group .note-hint-item {
            padding: 2px 4px !important;
            font-weight: 500;
            color: #535353;
        }

        .note-statusbar {
            display: none;
        }

        .extrasmallfont {
            font-size: 17px !important;
            vertical-align: middle;
        }

        .div-end-line {
            padding: 5px 0px;
            border-top: 1px solid #a2a2a2;
        }

        #inquiry_filter_following_date_time,
        #inquiry_filter_search_type,
        #inquiry_filter_search_value,
        #inquiry_filter_stage_of_site,
        #inquiry_filter_material_sent_type,
        #inquiry_filter_sure_not_sure,
        #inquiry_filter_closing {
            padding: 0px 5px;
            border: 1px solid gainsboro;
            margin-bottom: 12px;
            margin-top: 12px;
        }

        #inquiry_quotation_amount_lable {
            padding: 2px 6px;
            margin-bottom: 12px;
            margin-top: 12px;

        }

        .pdiv-202 {
            /* color: white !important; */
            /* background-color: #e86bfa !important; */
            /* opacity: 1; */
            /* background-image: linear-gradient(to right,
                                    #e687de,
                                    #e687de 2px,
                                    #e86bfa 2px,
                                    #e86bfa) !important; */
            /* background-size: 4px 100% !important; */
        }

        .pdiv-202 a {

            /* color: white !important; */

        }

        .pdiv-302 {
            /*color: white !important;
                            background-color: #9a4052 !important;
                            opacity: 1;
                            background-image: linear-gradient(to right, #742f51, #8a4c60 2px, #812c2c 2px, #7c4455) !important;
                            background-size: 4px 100% !important;*/
        }

        .pdiv-302 a {

            /*        color: white !important;*/

        }

        .update-message-text {
            white-space: pre-wrap;
        }

        .border-box {
            border: 0.1px solid #fefefe !important;
            border-radius: 6px;
            margin-top: 2px;
            padding-top: 2px;
            padding-left: 2px;
            background: white;
            padding: 1px 5px;
        }

        .btn-quotation {
            padding: 1px 3px !important;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .input-followup-date-time {
            margin-bottom: 8px;
            background: #c9e5fe;
            padding: 0px 5px;
            border: 1px solid gainsboro;
        }

        .input-closing-date-time {
            margin-bottom: 8px;
            background: #c9e5fe;
            padding: 0px 5px;
            border: 1px solid gainsboro;
            float: right;
            width: 30%;
            margin-top: -24px;
            font-size: 10px;
        }


        .select-change-status {
            margin-bottom: 2px;
            background: #090a09;
            border: 0;
            padding: 0 5px;
            color: white;
            font-size: 14px;
            width: 84%;
        }

        .btn-detail {
            padding: 1px 3px !important;
            text-transform: uppercase;
        }

        .btn-edit-detail {
            padding: 1px 3px !important;
            text-transform: uppercase;
        }

        .btn-change-assigned {
            padding: 1px 3px !important;
            text-transform: uppercase;
        }

        table.dataTable.nowrap td {
            background: #f1f1f1;
        }

        #datatable tbody,
        #datatable td,
        #datatable tfoot,
        #datatable tr {
            border-color: black;
        }

        .lable-inquiry-id {
            background: black;
            color: white;
            padding: 0 6px;
            border-radius: 5px;
        }

        .lable-status {
            background: lightblue;
            color: black;
            padding: 0 6px;
            border-radius: 5px;
        }

        .lable-inquiry-phone {
            background: white;
            color: black;
        }

        .lable-inquiry-quotation {
            background: white;
            color: black;
        }

        #datatable.table> :not(caption)>*>* {
            padding: 5px 6px !important;
        }

        body {
            /* Set "my-sec-counter" to 0 */
            counter-reset: inquiry-question-counter;
        }

        .inquiry-questions-lable::before {
            counter-increment: inquiry-question-counter;
            content: ""counter(inquiry-question-counter) ". ";
        }

        .has-no-followupdatetime {
            color: white;

            background-color: #ff8f8f;
            opacity: 1;
            background-size: 4px 4px;
            background-image: repeating-linear-gradient(45deg,
                    #f74545 0,
                    #f74545 0.4px,
                    #ff8f8f 0,
                    #ff8f8f 50%);
        }

        .expired-followupdatetime {
            color: white;

            background-color: #ff8f8f;
            opacity: 1;
            background-size: 4px 4px;
            background-image: repeating-linear-gradient(45deg,
                    #f74545 0,
                    #f74545 0.4px,
                    #ff8f8f 0,
                    #ff8f8f 50%);
        }

        .expired-closingdatetime {
            color: white;

            background-color: #ff8f8f;
            opacity: 1;
            background-size: 4px 4px;
            background-image: repeating-linear-gradient(45deg,
                    #f74545 0,
                    #f74545 0.4px,
                    #ff8f8f 0,
                    #ff8f8f 50%);
        }

        #datatable .select2-container .select2-selection--single .select2-selection__rendered {
            line-height: 22px;
        }

        #datatable .select2-container .select2-selection--single {
            height: 22px;
        }

        #datatable .select2-container--default .select2-selection--single .select2-selection__arrow b {
            margin-top: -9px;
        }

        .createdicon {
            float: right;
            color: black;
        }

        .inquiry-status-btn {
            border: 0 !important;
        }

        .inquiry-status-btn:hover {
            color: white !important;
        }

        .status-tab-active {
            text-decoration: underline !important;
            font-size: 15px;
            border: 2px solid black !important;
        }

        .inquiry-comments-icon {
            font-size: 20px;
        }

        .inquiry-update-badge {
            color: white;
            background: #495057;
            border: 0.5px solid white;
        }

        .inquiry-update-text {
            overflow: hidden;
            text-overflow: ellipsis;
            /* white-space: nowrap; */
            max-width: 88%;
            white-space: pre-wrap;
        }

        .inquiry-update-time {
            width: 10%;
        }

        .inquiry-update-box {
            display: block;
            padding: 14px 16px;
            color: #74788d;
            -webkit-transition: all 0.4s;
            transition: all 0.4s;
            border-top: 1px solid #eff2f7;
            border-radius: 4px;
        }

        .inquiry-update-reply-box {
            display: block;
            padding: 14px 16px;
            color: #74788d;
            -webkit-transition: all 0.4s;
            transition: all 0.4s;
            border-top: 1px solid #eff2f7;
            border-radius: 4px;
        }

        .inquiry-avatar-xs {
            min-width: 2rem !important;
        }

        .hightlight-update {
            color: #50a5f1;
        }

        .hightlight-update>.inquiry-update-badge {
            background-color: #50a5f1;
        }

        .reply-box {
            display: none;
        }

        .seen-ul {
            padding: 0;
        }

        ul.list-unstyled.chat-list.seen-ul a {
            padding: 4px 10px;
        }

        .seen-ul li {
            background: white !important;
            padding: 0;
            font-size: 10px;
        }

        .tooltip-inner .seen-avatar {
            background-color: rgba(85, 110, 230, 0.25) !important;
        }

        #datatable_processing {
            background: black;

            z-index: 9;
            top: 200px;
        }

        div.dataTables_wrapper div.dataTables_processing {
            width: auto;
            padding: 0;
        }

        .requestedforveify {
            background: black;
            color: white;
            padding: 2px 3px;
            border-radius: 4px;
        }

        #totalQuotationAmount {
            font-weight: 600;
        }


        .advance-filter-btn {
            margin-top: 5px;
        }

        .advance-filter-btn i {
            font-size: 20px;


        }

        .advance_filter_type {
            padding: 0px 5px;
            border: 1px solid gainsboro;
            margin-bottom: 6px;
            margin-top: 6px;
            width: 50%;
            margin-right: 5px;
        }

        .advance_filter_text {

            padding: 0px 5px;
            border: 1px solid gainsboro;
            margin-bottom: 6px;
            margin-top: 6px;
            width: 48%;

        }

        .advance-filter .dropdown-menu {
            width: 500px;
        }

        #advanceFilterInfo {
            font-size: 10px;
            color: gray;
        }

        .advance-filter .card {
            margin-bottom: 0px;
        }


        .advance-filter-btn:focus {
            box-shadow: none;
        }

        #btnAddAdvanceFilter {
            margin-left: 10px;
        }


        .select_stage_of_site {
            font-size: 10px;
        }

        .dtfc-fixed-left {
            z-index: 9;
        }

        .table-view-type {
            margin-left: 5px;
            padding: 0.1rem 0.3rem;
            margin-top: 11px;

        }

        span.closing-badge {
            border-radius: 7px !important;
            background: black;
            height: 19px;
            margin-top: 6px;
            color: white;
            width: 20px;
            text-align: center;
            margin-right: 1px;
            padding: 0 4px;
            margin-left: 3px;


        }

        .closing-expired {
            background: #f07d7d;
            color: white;
        }

        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_filter {
            display: inline-block;
            vertical-align: middle;
        }

        .dataTables_wrapper .dataTables_filter {
            float: right;
            margin-left: 10px; /* Adjust margin as needed */
        }
    </style>

    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Marketing Lead Report</h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <select id="inquiry_filter_search_type" name="inquiry_filter_search_type">
                                <option value="1">Client Name </option>
                                <option value="2">#ID </option>
                                <option value="3">Phone number</option>
                                <option value="4">Email</option>
                                <option value="5">City</option>
                                <option value="6">House No </option>
                                <option value="7">Address </option>
                                <option value="8">Area </option>
                                <option value="9">Assigned To</option>

                                <option value="10">Architect</option>
                                <option value="11">Electrician</option>

                                <option value="12">Source Type</option>
                                <option value="13">Source </option>
                                <option value="14">Site Stage </option>
                                <option value="15">Squre Foot </option>
                                <option value="16">Created By </option>
      
                                <option value="0">All </option>

                            </select>
                            <input type="text" class="" id="inquiry_filter_search_value"
                            name="inquiry_filter_search_value" placeholder="Search" value="">

                            <table id="datatable" class="table dt-responsive  nowrap w-100 table-grid-view">
                                <thead>
                                    <tr>
                                        <th>Name/Mobile/Email/Address</th>
                                        <th>Created By/Source</th>
                                        <th>Site Stage/Quotation</th>
                                        <th>Assigned/Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div> <!-- end col -->
            </div>
            <!--̧̧end row-->
        </div>
        <!-- container-fluid -->
    </div>
    <!-- End Page-content -->
        </div>
        <!-- container-fluid -->
    </div>
    <!-- End Page-content -->
    @csrf
@endsection('content')

@section('custom-scripts')
    <script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

    <!-- include summernote css/js -->

    <script src="{{ asset('assets/libs/summernote/summernote.min.js') }}"></script>


    <script type="text/javascript">
        var ajaxURL = "{{ route('crm.marketing.lead.report.ajax') }}";
        var csrfToken = $("[name=_token]").val();
        var inquiryPageLength = getCookie('inquiryPageLength') !== undefined ? getCookie('inquiryPageLength') : 10;
        var scrollTopHeightDataTable = 0;
        var scrollTopHeightModalDetail = 0;
        var inquiryViewType = getCookie('inquiryViewType') !== undefined ? getCookie('inquiryViewType') : 0;
        
        var table = $('#datatable').DataTable({
            //  "aoColumnDefs": [{ "bSortable": false, "aTargets": [5] }],
            "dom": '<"top"if>rt<"bottom"lip><"clear">',
            "scrollY": function() {
                return $(window).height() - 400;
            },

            "searching": true,
            "scrollCollapse": true,
            "fixedColumns": {
                left: 1,
            },
            "order": [
                [0, 'desc']
            ],
            "processing": true,
            "serverSide": true,
            "language": {
                "processing": '<button type="button" class="btn btn-dark waves-effect waves-light"><i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> Processing...</button>'
            },
            "pageLength": parseInt(inquiryPageLength),
            "ajax": {
                "url": ajaxURL,
                "type": "POST",
                "data": {
                    "_token": csrfToken,
                    "inquiry_filter_search_type": function() {
                        return $("#inquiry_filter_search_type").val();
                    },
                    "inquiry_filter_search_value": function() {
                        return $("#inquiry_filter_search_value").val();
                    },
                }
            },
            "aoColumns": [
                {
                    "mData": "name"
                },
                {
                    "mData": "created_by"
                },
                {
                    "mData": "site_stage"
                },
                {
                    "mData": "status"
                },
            ],
            "drawCallback": function() {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll(
                    '[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });

                if (scrollTopHeightDataTable != 0) {
                    // var scrollHeight=$("#inquiry-id-"+scrollInquiryId).offset().top - $("#inquiry-id-"+scrollInquiryId).offsetParent().offset().top
                    $(".dataTables_scrollBody").animate({
                        scrollTop: scrollTopHeightDataTable
                    }, 10);
                }
            }
        });

        $('#datatable').on('length.dt', function(e, settings, len) {
            setCookie('inquiryPageLength', len, 100);
        });

        $('#inquiry_filter_search_type').on('change', function() {
            table.ajax.reload();
        });

        $('#inquiry_filter_search_value').on('keyup', function() {
            table.ajax.reload();
        });

        var currentURL = window.location.href;
        var loadedURLLink = $('.userscomman a[href="' + currentURL + '"]');
        $(loadedURLLink).removeClass('status-tab-active');
        $(loadedURLLink).addClass('status-tab-active');
    </script>
@endsection


