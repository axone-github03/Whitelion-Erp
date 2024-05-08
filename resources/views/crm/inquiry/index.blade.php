@extends('layouts.main')
@section('title', $data['title'])
@section('content')
    @php
        $inquiryStatus = getInquiryStatus();
    @endphp



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

        @foreach ($inquiryStatus as $key => $value)
            .inquiry-status-lable-color-{{ $value['id'] }} {
                background: {{ $value['background'] }};
                color: {{ $value['color'] }};
            }

        @endforeach
    </style>

    <div class="page-content">
        <div class="container-fluid">
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Inquiry

                        </h4>




                        <div class="page-title-right">



                            @if ($data['isSalePerson'] == 1)
                                <a href="{{ route('inquiry.pending') }}?is_verified=0"
                                    class="btn @if ($data['no_of_inquiry_request'] > 0) btn-danger
                                         @else
                                         btn-dark @endif waves-effect waves-light">Pending
                                    Request : {{ $data['no_of_inquiry_request'] }}</a>
                            @endif

                            @if ($data['isChannelPartner'] != 0)
                                <a href="{{ route('inquiry.pending') }}?is_verified=2"
                                    class="btn btn-dark waves-effect waves-light">Rejected Request </a>
                            @endif

                            <button id="addBtnInquiry" class="btn btn-primary waves-effect waves-light"
                                data-bs-toggle="modal" data-bs-target="#modalInquiry" role="button"><i
                                    class="bx bx-plus font-size-16 align-middle me-2"></i>Inquiry</button>




                        </div>
                    </div>
                </div>
            </div>
            <!-- end page title -->

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">

                            <input type="hidden" name="selected_status" id="selected_status" value="{{ $data['status'] }}">







                            <div class="userscomman">

                                @if ($data['isTaleSalesUser'] == 1 || $data['isAdminOrCompanyAdmin'] == 1)
                                    <a href="{{ route('inquiry.exhibition') }}"
                                        class="btn btn-sm inquiry-status-btn inquiry-status-lable-color-0">Exhibition</a>
                                @endif

                                @foreach ($inquiryStatus as $key => $value)
                                    @if ($data['isArchitect'] == 1 && $value['can_display_on_inquiry_architect'] == 1)
                                        <a href="{{ route('inquiry') }}?status={{ $value['id'] }}"
                                            class="btn btn-sm   inquiry-status-btn inquiry-status-lable-color-{{ $value['id'] }}">{{ $value['name'] }}</a>
                                    @elseif($data['isElectrician'] == 1 && $value['can_display_on_inquiry_electrician'] == 1)
                                        <a href="{{ route('inquiry') }}?status={{ $value['id'] }}"
                                            class="btn btn-sm   inquiry-status-btn inquiry-status-lable-color-{{ $value['id'] }}">{{ $value['name'] }}</a>
                                    @elseif($data['isSalePerson'] == 1 && $value['can_display_on_inquiry_sales_person'] == 1)
                                        <a href="{{ route('inquiry') }}?status={{ $value['id'] }}"
                                            class="btn btn-sm  inquiry-status-btn inquiry-status-lable-color-{{ $value['id'] }}">{{ $value['name'] }}</a>
                                    @elseif($data['isAdminOrCompanyAdmin'] == 1 && $value['can_display_on_inquiry_user'] == 1)
                                        <a href="{{ route('inquiry') }}?status={{ $value['id'] }}"
                                            class="btn btn-sm  inquiry-status-btn inquiry-status-lable-color-{{ $value['id'] }}">{{ $value['name'] }}</a>
                                    @elseif($data['isChannelPartner'] != 0 && $value['can_display_on_inquiry_channel_partner'] == 1)
                                        <a href="{{ route('inquiry') }}?status={{ $value['id'] }}"
                                            class="btn btn-sm  inquiry-status-btn inquiry-status-lable-color-{{ $value['id'] }}">{{ $value['name'] }}</a>
                                    @elseif($data['isThirdPartyUser'] == 1 && $value['can_display_on_inquiry_third_party'] == 1)
                                        <a href="{{ route('inquiry') }}?status={{ $value['id'] }}"
                                            class="btn btn-sm  inquiry-status-btn inquiry-status-lable-color-{{ $value['id'] }}">{{ $value['name'] }}</a>
                                    @elseif($data['isTaleSalesUser'] == 1 && $value['can_display_on_inquiry_tele_sales'] == 1)
                                        <a href="{{ route('inquiry') }}?status={{ $value['id'] }}"
                                            class="btn btn-sm  inquiry-status-btn inquiry-status-lable-color-{{ $value['id'] }}">{{ $value['name'] }}</a>
                                    @endif
                                @endforeach





                            </div>

                            <a class="btn btn-dark float-end table-view-type" href="#" id="listViewBtn"><i
                                    class="bx bx-list-ul"></i></a>
                            <a class="btn btn-dark float-end table-view-type" href="#" id="GridViewBtn"><i
                                    class="bx bx-grid-alt"></i></a>

                            @if ($data['isArchitect'] == 0 && $data['isElectrician'] == 0)

                                <select id="inquiry_filter_following_date_time" name="inquiry_filter_following_date_time">
                                    <option value="0">All Following Date & Time </option>
                                    <option value="1">Missed Following Date & Time</option>
                                    <option value="2">2 Days Following Date & Time</option>
                                </select>

                                <select id="inquiry_filter_stage_of_site" name="inquiry_filter_stage_of_site">
                                    <option value="0">All Stage Of Site </option>
                                    <option value="">Not Selected </option>

                                    @foreach ($data['stage_of_site'] as $stagesite)
                                        <option value="{{ $stagesite->option }}">{{ $stagesite->option }}</option>
                                    @endforeach



                                </select>

                                @if ($data['status'] == 9)
                                    <select id="inquiry_filter_material_sent_type" name="inquiry_filter_material_sent_type">
                                        <option value="0">All</option>
                                        <option value="1">Material Sent</option>
                                        <option value="2">Claimed </option>
                                        <option value="3">Invoice Attached</option>
                                        <option value="4">Material Sent + Invoice Attached</option>


                                    </select>
                                @endif

                                @if ($data['status'] == 8)
                                    <select id="inquiry_filter_closing" name="inquiry_filter_closing">
                                        <option value="0">All Closing</option>
                                        <option value="1">In This Week</option>
                                        <option value="2" selected>In This Month </option>
                                        <option value="3">In Next Month</option>
                                        <option value="4">In Next Two Month</option>
                                        <option value="5">In Next Three Month</option>

                                    </select>

                                    <select id="inquiry_filter_sure_not_sure" name="inquiry_filter_sure_not_sure">
                                        <option value="0" selected>All</option>
                                        <option value="1">Sure </option>
                                        <option value="2">Not Sure </option>
                                        <option value="3">Quotation Pending</option>
                                    </select>
                                @endif



                                <select id="inquiry_filter_search_type" name="inquiry_filter_search_type">
                                    <option value="1">Name </option>
                                    <option value="2">#ID </option>
                                    <option value="3">Phone number</option>
                                    <option value="4">Pincode</option>
                                    <option value="5">City</option>
                                    <option value="6">House No </option>
                                    <option value="7">Building/Society name </option>
                                    <option value="8">Area </option>
                                    <option value="9">Assigned To</option>

                                    <option value="10">Architect</option>
                                    <option value="14">Architect Non Prime </option>
                                    <option value="15">Architect Prime</option>



                                    <option value="11">Electrician</option>
                                    <option value="16">Electrician Non Prime </option>
                                    <option value="17">Electrician Prime</option>

                                    <option value="12">Source Type</option>
                                    <option value="13">Source </option>
                                    <option value="18">Not Selected - Architect </option>
                                    <option value="19">Not Selected - Electrician </option>

                                    <option value="0">All </option>

                                </select>
                                <input type="text" class="" id="inquiry_filter_search_value"
                                    name="inquiry_filter_search_value" placeholder="Search" value="">


                                <div class="float-end">
                                    <div class="dropdown ">
                                        <button type="button" id="inquiry_quotation_amount_lable"
                                            class="btn-sm btn btn-outline-dark waves-effect waves-light float-end"
                                            data-bs-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">Quotation Amount: <span
                                                id="totalQuotationAmount">0</span></button>

                                        <div class="dropdown-menu dropdown-menu-end">

                                            <div class="card" style="margin-bottom: 0;">
                                                <div class="card-body">

                                                    <h4 class="card-title">Quotation Amount </h4>

                                                    <input type="int" id="inquiry_quotation_filter" class="">
                                                    <br>
                                                    <button type="button" id="btnClearQuotationFilter"
                                                        class="btn btn-sm btn-danger float-end"> Clear </button>


                                                </div>

                                            </div>

                                        </div>
                                    </div>
                                </div>






                            @endif


                            @if ($data['isArchitect'] == 0 && $data['isElectrician'] == 0)
                                <div class="float-end">
                                    <div class="dropdown advance-filter">


                                        <button data-bs-auto-close="outside" type="button"
                                            class="btn advance-filter-btn" data-bs-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false"><i class="bx bx-filter-alt "></i> </button>
                                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-lg">

                                            <div class="card">
                                                <div class="card-body">

                                                    <h4 class="card-title">Advanced filters <span class="float-end"
                                                            id="advanceFilterInfo"></span></h4>

                                                    <div class="dropdown-item-text" id="advanceFilterContent">



                                                    </div>
                                                    <br>

                                                    <button type="button" id="btnAddAdvanceFilter"
                                                        class="btn btn-sm btn-primary  float-end"><i
                                                            class="bx bx-plus "></i> Add</button>
                                                    <button type="button" id="btnClearAdvanceFilter"
                                                        class="btn btn-sm btn-danger float-end"> Clear All</button>

                                                </div>

                                            </div>







                                        </div>
                                    </div>
                                </div>
                            @endif



                            <table id="datatable" class="table dt-responsive  nowrap w-100 table-grid-view">
                                <thead>
                                    <tr>

                                        <th>Id - Name/Mobile/Address</th>
                                        <th>
                                            @if ($data['isArchitect'] == 1 || $data['isElectrician'] == 1)
                                                CreatedBy/Source/Architect
                                            @else
                                                CreatedBy/Source/Architect/Electrician
                                            @endif
                                        </th>
                                        <th>
                                            @if ($data['isArchitect'] == 1 || $data['isElectrician'] == 1)
                                                Stage of site
                                            @else
                                                Stage of site/Quotation/Follow Up
                                            @endif

                                        </th>
                                        <th>
                                            @if ($data['isArchitect'] == 1 || $data['isElectrician'] == 1)
                                                Status
                                            @else
                                                Assigned/Status/Detail
                                            @endif
                                        </th>

                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>

                            <table id="datatable" class="table   nowrap w-100 table-list-view">

                                <thead>
                                    <tr>

                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Address </th>
                                        <th>Status&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        </th>
                                        <th>Stage Of Site </th>
                                        @if ($data['isArchitect'] == 0 && $data['isElectrician'] == 0)
                                            <th>Followup Type&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                                            <th>Followup Date &
                                                Time&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            </th>

                                            <th>Closing
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            </th>
                                        @endif

                                        <th>Architect </th>

                                        @if ($data['isArchitect'] == 0 && $data['isElectrician'] == 0)
                                            <th>Electrician </th>
                                        @endif

                                        <th>Source</th>

                                        @if ($data['isArchitect'] == 0 && $data['isElectrician'] == 0)
                                            <th>Assigned&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            </th>
                                        @endif


                                        <th>Created By
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        </th>

                                        @if ($data['isArchitect'] == 0 && $data['isElectrician'] == 0)
                                            <th>Quotation
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            </th>

                                            <th>Billing&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            </th>
                                        @endif




















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


    <div class="modal fade" id="modalDetail" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
        role="dialog" aria-labelledby="modalDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetailLabel">Detail </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <input type="hidden" name="detail_inquiry_id" id="detail_inquiry_id">





                <div class="modal-body" id="modelBodyDetail">

                    <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#inquiry_update"
                                onclick="loadDetail('inquiry_update')" role="tab">
                                <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                                <span class="d-none d-sm-block">Update</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#inquiry_files"
                                onclick="loadDetail('inquiry_files')" role="tab">
                                <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                                <span class="d-none d-sm-block">Files</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#inquiry_log"
                                onclick="loadDetail('inquiry_log')" role="tab">
                                <span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
                                <span class="d-none d-sm-block">Activity Log</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#inquiry_answer"
                                onclick="loadDetail('inquiry_answer')" role="tab">
                                <span class="d-block d-sm-none"><i class="fas fa-cog"></i></span>
                                <span class="d-none d-sm-block">Answer</span>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3 text-muted">
                        <div class="tab-pane active" id="inquiry_update" role="tabpanel">

                        </div>
                        <div class="tab-pane" id="inquiry_files" role="tabpanel">

                        </div>
                        <div class="tab-pane" id="inquiry_log" role="tabpanel">

                        </div>
                        <div class="tab-pane" id="inquiry_answer" role="tabpanel">

                        </div>
                    </div>



                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>

                </div>


            </div>
        </div>
    </div>


    <!-- start inquiry status change model-->
    <div class="modal fade" id="modalStatusChange" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
        role="dialog" aria-labelledby="modalStatusChangeLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"> Change Inquiry Status </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formInquiryStatusChange" action="{{ route('inquiry.answer.save') }}" method="POST"
                    enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf

                    <div class="col-md-12 text-center loadingcls">






                        <button type="button" class="btn btn-light waves-effect">
                            <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                        </button>


                    </div>

                    <div class="modal-body" id="inquiry_question_body">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button id="formInquiryStatusChangeSave" type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>


            </div>
        </div>
    </div>


    <div class="modal fade" id="modalAssignedToChange" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
        role="dialog" aria-labelledby="modalAssignedToChangeLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"> Assigned To </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formAssignedTo" action="{{ route('inquiry.assignedto.save') }}" method="POST"
                    enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf

                    <input type="hidden" name="assigned_to_inquiry_id" id="assigned_to_inquiry_id">

                    <div class="col-md-12 text-center loadingcls">






                        <button type="button" class="btn btn-light waves-effect">
                            <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                        </button>


                    </div>

                    <div class="modal-body">

                        <div class="row">
                            <div class="col-md-12">

                                <div class="mb-3 ajax-select mt-3 mt-lg-0">
                                    <label class="form-label">Assigned To </label>
                                    <select class="form-control select2-ajax" id="inquiry_change_assigned_to"
                                        name="inquiry_change_assigned_to" required>
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select assigned to.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button id="submit" type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>


            </div>
        </div>
    </div>

    <div class="modal fade" id="modalQuotation" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
        role="dialog" aria-labelledby="modalQuotationLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalQuotationLabel">Quotation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formQuotation" action="{{ route('inquiry.quotation.save') }}" method="POST"
                    enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf

                    <input type="hidden" name="quotation_inquiry_id" id="quotation_inquiry_id">

                    <div class="col-md-12 text-center loadingcls">






                        <button type="button" class="btn btn-light waves-effect">
                            <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                        </button>


                    </div>

                    <div class="modal-body">


                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="inquiry_quotation" class="form-label">Quotation <code
                                            class="highlighter-rouge"></code></label>
                                    <input type="file" class="form-control" id="inquiry_quotation"
                                        name="inquiry_quotation" value="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="inquiry_quotation_amount" class="form-label">Quotation Amount <code
                                            class="highlighter-rouge">*</code></label>
                                    <input type="number" class="form-control" id="inquiry_quotation_amount"
                                        name="inquiry_quotation_amount" placeholder="Quotation Amount" value=""
                                        required>
                                </div>
                            </div>
                        </div>




                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button id="submit" type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>


            </div>
        </div>
    </div>


    <div class="modal fade" id="modalBillingInvoice" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
        role="dialog" aria-labelledby="modalBillingInvoiceLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalBillingInvoiceLabel">Billing Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalBillingInvoiceBody">

                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="modalUpdateTM" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
        role="dialog" aria-labelledby="modalUpdateTMLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered " role="document">
            <div class="modal-content">

                <form id="formUpdateTM" action="{{ route('inquiry.tm.save') }}" method="POST"
                    enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="modal-body" id="modalUpdateTMBody">
                        <input type="hidden" name="update_TM_inquiry_id" id="update_TM_inquiry_id">
                        <input type="hidden" name="update_TM_type" id="update_TM_type">

                        @csrf

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="update_TM_message" class="form-label">Update</label>
                                    <textarea id="update_TM_message" name="update_TM_message" class="form-control" rows="3" required></textarea>
                                </div>
                            </div>
                        </div>


                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button id="submitUpdateTM" type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalBilling" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
        role="dialog" aria-labelledby="modalBillingLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalBillingLabel">Billing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formBilling" action="{{ route('inquiry.billing.save') }}" method="POST"
                    enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf

                    <input type="hidden" name="billing_inquiry_id" id="billing_inquiry_id">

                    <div class="col-md-12 text-center loadingcls">






                        <button type="button" class="btn btn-light waves-effect">
                            <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                        </button>


                    </div>

                    <div class="modal-body">


                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="inquiry_billing_invoice" class="form-label">Billing Invoice <code
                                            class="highlighter-rouge"></code></label>
                                    <input multiple type="file" class="form-control" id="inquiry_billing_invoice"
                                        name="inquiry_billing_invoice[]" value="">
                                </div>
                            </div>

                            @if ($data['isAdminOrCompanyAdmin'] == 1)
                                <div class="col-md-12" id="div_inquiry_billing_amount">
                                    <div class="mb-3">
                                        <label for="inquiry_quotation_amount" class="form-label">Billing Amount <code
                                                class="highlighter-rouge">*</code></label>
                                        <input type="number" class="form-control" id="inquiry_billing_amount"
                                            name="inquiry_billing_amount" placeholder="Billing Amount" value=""
                                            required>
                                    </div>
                                </div>
                            @endif

                        </div>




                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button id="submit" type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>


            </div>
        </div>
    </div>


    @csrf



@endsection('content')

@section('custom-scripts')
    <script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

    <!-- include summernote css/js -->

    <script src="{{ asset('assets/libs/summernote/summernote.min.js') }}"></script>


    @include('../crm/inquiry/comman/modal')
    <script type="text/javascript">
        var ajaxURL = "{{ route('inquiry.ajax') }}";
        var ajaxURLInquiryDetail = "{{ route('inquiry.detail') }}";
        var ajaxURLInquiryQuestions = "{{ route('inquiry.questions') }}";
        var ajaxURLInquiryAssignedTo = "{{ route('inquiry.assigned.user') }}";
        var ajaxURLInquirySeen = "{{ route('inquiry.update.seen') }}";
        var ajaxURLSearchInquiryAssignedTo = "{{ route('inquiry.search.assigned.user') }}";
        var ajaxURLInquiryFollowUpDateTime = "{{ route('inquiry.followup.datetime.save') }}";
        var ajaxURLInquiryClosingDateTime = "{{ route('inquiry.closing.datetime.save') }}";
        var ajaxURLInquiryPhoneNumber = "{{ route('inquiry.phone') }}";
        var ajaxURLInquiryInvoiceDelete = "{{ route('inquiry.delete.invoice') }}";
        var ajaxSaveStageOfSite = "{{ route('inquiry.stageofsite.save') }}";
        var ajaxSaveFollowUpType = "{{ route('inquiry.followuptype.save') }}";
        var ajaxURLSaveUpdate = "{{ route('inquiry.update.save') }}";
        var ajaxURLSearchMentionUsers = "{{ route('inquiry.search.mention.users') }}";
        var ajaxURLSureNotSure = "{{ route('inquiry.sure.notsure') }}";
        var ajaxURLTM = "{{ route('inquiry.tm') }}";
        var isArchitect = "{{ $data['isArchitect'] }}";
        var isElectrician = "{{ $data['isElectrician'] }}";
        var isChannelPartner = "{{ $data['isChannelPartner'] }}";
        var isAdminOrCompanyAdmin = "{{ $data['isAdminOrCompanyAdmin'] }}";
        var isThirdPartyUser = "{{ $data['isThirdPartyUser'] }}";
        var openInquiryId = "{{ $data['inquiry_id'] }}";
        var csrfToken = $("[name = _token ").val();
        var inquiryPageLength = getCookie('inquiryPageLength') !== undefined ? getCookie('inquiryPageLength') : 10;
        var scrollTopHeightDataTable = 0;
        var scrollTopHeightModalDetail = 0;
        var inquiryViewType = getCookie('inquiryViewType') !== undefined ? getCookie('inquiryViewType') : 0;



        $("#GridViewBtn").click(function(event) {
            event.preventDefault();

            setCookie('inquiryViewType', 0, 100);
            location.reload();



        });

        $("#listViewBtn").click(function(event) {
            event.preventDefault();

            setCookie('inquiryViewType', 1, 100);

            location.reload();


        });



        if (inquiryViewType == 0) {

            $("#GridViewBtn").hide();
            $(".table-list-view").remove();

            var table = $('#datatable').DataTable({
                //  "aoColumnDefs": [{ "bSortable": false, "aTargets": [5] }],
                "dom": '<"top"i>rt<"bottom"flp><"clear">',
                "scrollY": function() {
                    return $(window).height() - 400;
                },

                "searching": false,
                "scrollCollapse": true,
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
                        "view_type": 0,
                        "status": function() {
                            return $("#selected_status").val();
                        },
                        "inquiry_filter_following_date_time": function() {
                            return $("#inquiry_filter_following_date_time").val();
                        },
                        "inquiry_filter_search_type": function() {
                            return $("#inquiry_filter_search_type").val();
                        },
                        "inquiry_filter_search_value": function() {
                            return $("#inquiry_filter_search_value").val();
                        },
                        "inquiry_filter_stage_of_site": function() {
                            return $("#inquiry_filter_stage_of_site").val();
                        },
                        "inquiry_filter_material_sent_type": function(argument) {
                            return $("#inquiry_filter_material_sent_type").val();
                        },
                        "inquiry_filter_closing": function() {
                            return $("#inquiry_filter_closing").val();
                        },
                        "inquiry_filter_sure_not_sure": function() {
                            return $("#inquiry_filter_sure_not_sure").val();
                        },

                        "inquiry_quotation_filter": function() {
                            return $("#inquiry_quotation_filter").val();
                        },
                        "advance_filter_type": function() {

                            var advanceFilterType = [];
                            var advanceTypeKey = 0

                            $('.advance_filter_type').each(function() {
                                advanceFilterType[advanceTypeKey] = $(this).val();
                                advanceTypeKey++;
                            });

                            return JSON.stringify(advanceFilterType);
                        },
                        "advance_filter_text": function() {

                            var advanceFilterText = [];
                            var advanceTextKey = 0

                            $('.advance_filter_text').each(function() {
                                advanceFilterText[advanceTextKey] = $(this).val();
                                advanceTextKey++;
                            });

                            return JSON.stringify(advanceFilterText);
                        }


                    }



                },
                "aoColumns": [{
                        "mData": "first_name"
                    },
                    {
                        "mData": "created_by"
                    },
                    {
                        "mData": "follow_up"
                    },
                    {
                        "mData": "status"
                    }
                ],
                "drawCallback": function() {



                    $("#advanceFilterInfo").html($("#datatable_info").html());



                    $(".input-followup-time").select2();
                    $(".input-closing-time").select2();
                    if (openInquiryId != 0) {
                        callDetail(openInquiryId);
                        openInquiryId = 0;
                    }



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


        } else if (inquiryViewType == 1) {



            $("#listViewBtn").hide();
            $(".table-grid-view").remove();

            if (isArchitect == 0 && isElectrician == 0) {


                var table = $('#datatable').DataTable({
                    //  "aoColumnDefs": [{ "bSortable": false, "aTargets": [5] }],
                    "dom": '<"top"i>rt<"bottom"flp><"clear">',
                    "scrollY": function() {
                        return $(window).height() - 400;
                    },
                    "scrollX": true,
                    "searching": false,
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
                            "view_type": 1,
                            "status": function() {
                                return $("#selected_status").val()
                            },
                            "inquiry_filter_following_date_time": function() {
                                return $("#inquiry_filter_following_date_time").val()
                            },
                            "inquiry_filter_search_type": function() {
                                return $("#inquiry_filter_search_type").val()
                            },
                            "inquiry_filter_search_value": function() {
                                return $("#inquiry_filter_search_value").val()
                            },
                            "inquiry_filter_stage_of_site": function() {
                                return $("#inquiry_filter_stage_of_site").val()
                            },
                            "inquiry_filter_material_sent_type": function(argument) {
                                return $("#inquiry_filter_material_sent_type").val()
                            },
                            "inquiry_filter_closing": function() {
                                return $("#inquiry_filter_closing").val()
                            },
                            "inquiry_filter_sure_not_sure": function() {
                                return $("#inquiry_filter_sure_not_sure").val();
                            },
                            "inquiry_quotation_filter": function() {
                                return $("#inquiry_quotation_filter").val();
                            },
                            "advance_filter_type": function() {

                                var advanceFilterType = [];
                                var advanceTypeKey = 0

                                $('.advance_filter_type').each(function() {
                                    advanceFilterType[advanceTypeKey] = $(this).val();
                                    advanceTypeKey++;
                                });

                                return JSON.stringify(advanceFilterType);
                            },
                            "advance_filter_text": function() {

                                var advanceFilterText = [];
                                var advanceTextKey = 0

                                $('.advance_filter_text').each(function() {
                                    advanceFilterText[advanceTextKey] = $(this).val();
                                    advanceTextKey++;
                                });

                                return JSON.stringify(advanceFilterText);
                            }


                        }



                    },
                    "aoColumns":

                        [{
                                "mData": "name"
                            },
                            {
                                "mData": "phone_number"
                            },
                            {
                                "mData": "address"
                            },
                            {
                                "mData": "status"
                            },
                            {
                                "mData": "stage_of_site"
                            },
                            {
                                "mData": "view_follow_up_type"
                            },
                            {
                                "mData": "view_follow_up_date_time"
                            },
                            {
                                "mData": "closing"
                            },
                            {
                                "mData": "architect"
                            },
                            {
                                "mData": "electrician"
                            },
                            {
                                "mData": "source"
                            },
                            {
                                "mData": "assign"
                            },
                            {
                                "mData": "created_by"
                            },
                            {
                                "mData": "quotation"
                            },
                            {
                                "mData": "billing"
                            }

                        ]

                        ,
                    "drawCallback": function() {



                        $("#advanceFilterInfo").html($("#datatable_info").html());



                        $(".input-followup-time").select2();
                        $(".input-closing-time").select2();
                        if (openInquiryId != 0) {
                            callDetail(openInquiryId);
                            openInquiryId = 0;
                        }



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
            } else {



                var table = $('#datatable').DataTable({
                    //  "aoColumnDefs": [{ "bSortable": false, "aTargets": [5] }],
                    "dom": '<"top"i>rt<"bottom"flp><"clear">',
                    "scrollY": function() {
                        return $(window).height() - 400;
                    },
                    "scrollX": true,
                    "searching": false,
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
                            "view_type": 1,
                            "status": function() {
                                return $("#selected_status").val()
                            },
                            "inquiry_filter_following_date_time": function() {
                                return $("#inquiry_filter_following_date_time").val()
                            },
                            "inquiry_filter_search_type": function() {
                                return $("#inquiry_filter_search_type").val()
                            },
                            "inquiry_filter_search_value": function() {
                                return $("#inquiry_filter_search_value").val()
                            },
                            "inquiry_filter_stage_of_site": function() {
                                return $("#inquiry_filter_stage_of_site").val()
                            },
                            "inquiry_filter_material_sent_type": function(argument) {
                                return $("#inquiry_filter_material_sent_type").val()
                            },
                            "inquiry_quotation_filter": function() {
                                return $("#inquiry_quotation_filter").val();
                            },
                            "inquiry_filter_closing": function() {
                                return $("#inquiry_filter_closing").val()
                            },
                            "inquiry_filter_sure_not_sure": function() {
                                return $("#inquiry_filter_sure_not_sure").val();
                            },
                            "advance_filter_type": function() {

                                var advanceFilterType = [];
                                var advanceTypeKey = 0

                                $('.advance_filter_type').each(function() {
                                    advanceFilterType[advanceTypeKey] = $(this).val();
                                    advanceTypeKey++;
                                });

                                return JSON.stringify(advanceFilterType);
                            },
                            "advance_filter_text": function() {

                                var advanceFilterText = [];
                                var advanceTextKey = 0

                                $('.advance_filter_text').each(function() {
                                    advanceFilterText[advanceTextKey] = $(this).val();
                                    advanceTextKey++;
                                });

                                return JSON.stringify(advanceFilterText);
                            }


                        }



                    },
                    "aoColumns":

                        [{
                                "mData": "name"
                            },
                            {
                                "mData": "phone_number"
                            },
                            {
                                "mData": "address"
                            },
                            {
                                "mData": "status"
                            },
                            {
                                "mData": "stage_of_site"
                            },
                            {
                                "mData": "architect"
                            },
                            {
                                "mData": "source"
                            },
                            {
                                "mData": "created_by"
                            }

                        ]

                        ,
                    "drawCallback": function() {



                        $("#advanceFilterInfo").html($("#datatable_info").html());



                        $(".input-followup-time").select2();
                        $(".input-closing-time").select2();
                        if (openInquiryId != 0) {
                            callDetail(openInquiryId);
                            openInquiryId = 0;
                        }



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

            }

        }



        function reloadTable() {
            table.ajax.reload(null, false);
        }


        table.on('xhr', function() {
            if (isArchitect == 0 && isElectrician == 0) {
                var responseData = table.ajax.json();
                $("#totalQuotationAmount").html(responseData['quotationAmount']);

            }



        });

        $("#inquiry_assigned_to").select2({
            ajax: {
                url: ajaxURLSearchInquiryAssignedTo,
                dataType: 'json',
                delay: 0,
                data: function(params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                        city_id: function() {
                            return $("#inquiry_city_id").val();
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
            placeholder: 'Search for a assigned to',
            dropdownParent: $("#modalInquiry .modal-body")
        });

        $("#inquiry_change_assigned_to").select2({
            ajax: {
                url: ajaxURLSearchInquiryAssignedTo,
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
            placeholder: 'Search for a assigned to',
            dropdownParent: $("#modalAssignedToChange .modal-body")
        });

        function changeFollowUpDateTime(id) {
            $("#save_answer_follow_up_date_time_" + id).show();
        }

        function changeClosingUpDateTime(id) {
            $("#save_answer_closing_date_time_" + id).show();
        }

        function moveToSure(id, is_predication_sure) {
            if (is_predication_sure == 1) {
                var confirmButtonText = "Yes, mark as sure !"
                var successMessage = "Successfully mark as sure";

            } else if (is_predication_sure == 0) {
                var confirmButtonText = "Yes, mark as not sure !"
                var successMessage = "Successfully mark as not sure";
            }


            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: !0,
                confirmButtonText: confirmButtonText,
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
                            url: ajaxURLSureNotSure + "?inquiry_id=" + id +
                                "&is_predication_sure=" + is_predication_sure,
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
                        title: successMessage,
                        text: "Your record has been updated.",
                        icon: "success"
                    });

                }

            });

        }



        function moveToTM(id, TM) {
            if (TM == "T") {
                var confirmButtonText = "Yes, mark as TeleSales!"
                var successMessage = "Successfully mark as TeleSales";

            } else if (TM == "M") {
                var confirmButtonText = "Yes, mark as Manager !"
                var successMessage = "Successfully mark as Manager";
            }


            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: !0,
                confirmButtonText: confirmButtonText,
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
                            url: ajaxURLTM + "?inquiry_id=" + id + "&TM=" + TM,
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
                        title: successMessage,
                        text: "Your record has been updated.",
                        icon: "success"
                    });

                }

            });

        }


        function removeTOTM(id, TM) {

            $("#modalUpdateTM").modal('show');
            $("#update_TM_inquiry_id").val(id);
            $("#update_TM_type").val(TM);

        }







        $("body").delegate(".seen-btn", "mouseenter", function() {


            var InquiryUpdateId = this.id;
            var piecesOfInquiryUpdateId = InquiryUpdateId.split('-');




            $("#" + InquiryUpdateId).tooltip('dispose').attr('title',
                "<i class='bx bx-loader bx-spin font-size-16 align-middle me-2'></i>");
            $("#" + InquiryUpdateId).tooltip('show');




            $.ajax({
                type: 'GET',
                url: ajaxURLInquirySeen + "?update_id=" + piecesOfInquiryUpdateId[2],
                success: function(resultData) {


                    if (resultData['status'] == 1) {

                        $("#" + InquiryUpdateId).tooltip('dispose').attr('title', resultData['data']);
                        $("#" + InquiryUpdateId).tooltip('show');

                    }
                }
            });



        });


        $("#datatable").delegate(".save_answer_follow_up_date_time", "click", function() {


            var splitId = this.id.split("_");
            var followUpDate = $("#answer_follow_up_date_" + splitId[6]).val();
            var followUpTime = $("#answer_follow_up_time_" + splitId[6]).val();



            $.ajax({
                type: 'POST',
                url: ajaxURLInquiryFollowUpDateTime,
                data: {
                    "_token": csrfToken,
                    "inquiry_id": splitId[6],
                    "follow_up_date": followUpDate,
                    "follow_up_time": followUpTime,

                },
                success: function(resultData) {


                    if (resultData['status'] == 1) {

                        toastr["success"](resultData['msg']);

                        $("#save_answer_follow_up_date_time_" + splitId[6]).hide();
                        reloadTable();

                    } else {

                        toastr["error"](resultData['msg']);

                    }
                }
            });


        });






        $("#btnAddAdvanceFilter").click(function(event) {
            event.preventDefault();

            var advanceFilterContentStr = '<div class="advance-filter-section">';
            advanceFilterContentStr += '<select class="advance_filter_type" >';
            advanceFilterContentStr += '<option value="1">Name </option>'
            advanceFilterContentStr += '<option value="2">Phone number</option>';
            advanceFilterContentStr += '<option value="3">Pincode</option>';
            advanceFilterContentStr += '<option value="4">City</option>';
            advanceFilterContentStr += '<option value="5">House No </option>';
            advanceFilterContentStr += '<option value="6">Building/Society name </option>';
            advanceFilterContentStr += '<option value="7">Area </option>';
            advanceFilterContentStr += '<option value="8">Assigned To</option>';
            advanceFilterContentStr += '<option value="9">Architect</option>';
            advanceFilterContentStr += '<option value="10">Architect Non Prime </option>';
            advanceFilterContentStr += '<option value="11">Architect Prime</option>';
            advanceFilterContentStr += '<option value="12">Electrician</option>';
            advanceFilterContentStr += '<option value="15">Electrician Non Prime </option>';
            advanceFilterContentStr += '<option value="16">Electrician Prime </option>';
            advanceFilterContentStr += '<option value="13">Source Type</option>'
            advanceFilterContentStr += '<option value="14">Source </option>'
            advanceFilterContentStr += '</select>';
            advanceFilterContentStr += '<input type="text" name="" class="advance_filter_text" >';
            advanceFilterContentStr += '</div>';

            $("#advanceFilterContent").append(advanceFilterContentStr);


        });

        $("#btnClearAdvanceFilter").click(function(event) {
            event.preventDefault();
            $("#advanceFilterContent").html("");
            table.ajax.reload();



        });

        $("#btnClearQuotationFilter").click(function(event) {
            event.preventDefault();
            $("#inquiry_quotation_filter").val("");
            table.ajax.reload();



        });



        $("#advanceFilterContent").delegate(".advance_filter_text", "keyup", function() {
            table.ajax.reload();
        });

        $("#advanceFilterContent").delegate(".advance_filter_type", "change", function() {
            table.ajax.reload();
        });





        $("#datatable").delegate(".save_answer_closing_date_time", "click", function() {


            var splitId = this.id.split("_");

            var closingUpDate = $("#answer_closing_date_" + splitId[5]).val();




            $.ajax({
                type: 'POST',
                url: ajaxURLInquiryClosingDateTime,
                data: {
                    "_token": csrfToken,
                    "inquiry_id": splitId[5],
                    "closing_date": closingUpDate,

                },
                success: function(resultData) {


                    if (resultData['status'] == 1) {

                        toastr["success"](resultData['msg']);
                        $("#save_answer_closing_date_time_" + splitId[6]).hide();
                        reloadTable();

                    } else {

                        toastr["error"](resultData['msg']);

                    }
                }
            });


        });

        function saveInquiryUpdate(id, updateID) {

            if ($("#inquiry_update_message_" + updateID).summernote('code').trim() != "") {

                scrollTopHeightModalDetail = $('#modalDetail').prop('scrollTop');
                // console.log(scrollTopHeightModalDetail);
                if (updateID == 0) {
                    var updateSaveBtnLable = "Updating...";
                } else {
                    var updateSaveBtnLable = "Replying...";
                }
                $("#inquiry_update-save-" + updateID).html(updateSaveBtnLable);
                $("#inquiry_update-save-" + updateID).prop('disabled', true);

                $.ajax({
                    type: 'POST',
                    url: ajaxURLSaveUpdate,
                    data: {
                        "_token": csrfToken,
                        "inquiry_id": id,
                        "inquiry_update_id": updateID,
                        "message": $("#inquiry_update_message_" + updateID).summernote('code'),

                    },
                    success: function(resultData) {

                        $("#inquiry_update_message").val('');
                        scrollTopHeightDataTable = $('.dataTables_scrollBody').prop('scrollTop');


                        if (resultData['status'] == 1) {

                            toastr["success"](resultData['msg']);
                            loadDetail('inquiry_update');
                            table.ajax.reload(null, false);
                            if (updateID == 0) {
                                var updateSaveBtnLable = "Update"

                            } else {
                                var updateSaveBtnLable = "Reply"
                            }
                            $("#inquiry_update-save-" + updateID).html(updateSaveBtnLable);
                            $("#inquiry_update-save-" + updateID).prop('disabled', false);
                            $("#inquiry_update_message_" + updateID).val('');

                        } else {

                            toastr["error"](resultData['msg']);
                            if (updateID == 0) {
                                var updateSaveBtnLable = "Update"

                            } else {
                                var updateSaveBtnLable = "Reply"
                            }
                            $("#inquiry_update-save-" + updateID).html(updateSaveBtnLable);

                        }
                    }
                });
            } else {

                toastr["error"]("Please enter text before save");

            }

        }


        $('#modalDetail').on('hidden.bs.modal', function() {
            if (scrollTopHeightDataTable != 0) {
                // var scrollHeight=$("#inquiry-id-"+scrollInquiryId).offset().top - $("#inquiry-id-"+scrollInquiryId).offsetParent().offset().top
                $(".dataTables_scrollBody").animate({
                    scrollTop: scrollTopHeightDataTable
                }, 10);
            }
        });

        function changeAssingedTo(id) {

            $("#modalAssignedToChange .modal-title").html("Inquiry #" + id + " Assigned To");

            $("#modalAssignedToChange").modal('show');
            $("#formAssignedTo .loadingcls").show();

            $.ajax({
                type: 'GET',
                url: ajaxURLInquiryAssignedTo + "?inquiry_id=" + id,
                success: function(resultData) {

                    $("#formAssignedTo .loadingcls").hide();
                    if (resultData['status'] == 1) {

                        if (resultData['data'].length > 0) {

                            $("#inquiry_change_assigned_to").empty().trigger('change');


                            var newOption = new Option(resultData['data'][0]['full_name'], resultData['data'][0]
                                ['id'], false, false);

                            $('#inquiry_change_assigned_to').append(newOption).trigger('change');
                            $("#assigned_to_inquiry_id").val(id);

                        }




                    } else {

                        toastr["error"](resultData['msg']);

                    }
                }
            });


        }

        function loadDetail(type) {
            UIType = type;
            getDetail();

        }

        var UIType = 'inquiry_update';
        var previousUIType = '';

        function callDetail(id) {


            $('.nav a:first').tab('show');
            $("#modalDetailLabel").html("Inquiry #" + id + " (" + $("#inquiry-name-" + id).html() + ")");
            $("#modalDetail").modal('show');
            $("#detail_inquiry_id").val(id);
            UIType = 'inquiry_update';
            previousUIType = '';
            getDetail();

        }

        $(".pre-select2-apply").select2({
            minimumResultsForSearch: Infinity,
            dropdownParent: $("#modalInquiry .modal-body")
        });

        function getDetail() {

            if (previousUIType != UIType) {

                $("#" + UIType).html(
                    '<p class="mb-0"><div class="col-md-12 text-center loadingcls"><button type="button" class="btn btn-light waves-effect"><i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading</button></div></p>'
                );

            }


            var inquiryId = $("#detail_inquiry_id").val();
            $.ajax({
                type: 'GET',
                url: ajaxURLInquiryDetail + "?inquiry_id=" + inquiryId + '&ui_type=' + UIType,
                success: function(resultData) {

                    previousUIType = UIType;

                    $("#modalDetail .loadingcls").hide();
                    if (resultData['status'] == 1) {

                        $("#" + UIType).html(resultData['view']);

                        $('.inquiry_update_message').summernote({
                            disableResizeEditor: true,

                            toolbar: false,
                            height: 150,
                            hint: {
                                match: /\B@(\w*)$/,
                                users: function(keyword, callback) {
                                    $.ajax({
                                        url: ajaxURLSearchMentionUsers + "?q=" + keyword,
                                        type: 'get',
                                        async: true //This works but freezes the UI
                                    }).done(callback);
                                },
                                search: function(keyword, callback) {
                                    this.users(keyword, callback); //callback must be an array
                                },
                                content: function(item) {

                                    return '@' + item;
                                }
                            }
                        });

                        setTimeout(function() {




                            var tooltipTriggerList = [].slice.call(document.querySelectorAll(
                                '[data-bs-toggle="tooltip"]'))
                            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                                return new bootstrap.Tooltip(tooltipTriggerEl)
                            });


                        }, 500)


                        if (scrollTopHeightModalDetail != 0) {
                            $("#modalDetail").animate({
                                scrollTop: scrollTopHeightDataTable
                            }, 10);

                            scrollTopHeightModalDetail = 0;




                        }




                    } else {

                        toastr["error"](resultData['msg']);

                    }
                }
            });

        }



        function changeStatus(id, currentStatus) {


            $("#modalStatusChange .modal-title").html("Inquiry #" + id + " Change Inquiry Status");

            var newStatus = document.getElementById("inquiry_status_" + id).value;
            //  document.getElementById("inquiry_status_"+id).value = currentStatus;
            const $select = document.querySelector("#inquiry_status_" + id);
            $select.value = currentStatus;
            //$("#inquiry_status_"+id).val(currentStatus);
            $('#formInquiryStatusChange').trigger("reset");
            $("#formInquiryStatusChange").removeClass('was-validated');
            $("#modalStatusChange").modal('show');
            $("#inquiry_question_body").html("");
            $("#formInquiryStatusChange .loadingcls").show();


            $.ajax({
                type: 'GET',
                url: ajaxURLInquiryQuestions + "?&new_status=" + newStatus + "&inquiry_id=" + id,
                success: function(resultData) {
                    if (resultData['status'] == 1) {

                        $("#formInquiryStatusChange .loadingcls").hide();
                        $("#inquiry_question_body").html(resultData['view']);


                        $("#answer_architect").select2({
                            ajax: {
                                url: ajaxURLSearchArchitect,
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
                            placeholder: 'Search for architect',
                            dropdownParent: $("#modalStatusChange .modal-content")
                        });

                        $("#answer_electrician").select2({
                            ajax: {
                                url: ajaxURLSearchElectrician,
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
                            placeholder: 'Search for electrician',
                            dropdownParent: $("#modalStatusChange .modal-content")
                        });


                        $("#answer_material_send_channel_partner").select2({
                            ajax: {
                                url: ajaxURLSearchChannelPartner,
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
                            placeholder: 'Search for channel partner',
                            dropdownParent: $("#modalStatusChange .modal-content")
                        });





                        $('#answer_electrician').on('change', function() {


                            $.ajax({
                                type: 'GET',
                                url: ajaxURLSearchElectrician + "?user_id=" + $(this).val(),
                                success: function(resultData) {

                                    if (resultData['results'].length > 0) {


                                        $("#answer_electrician_phone_number").val(
                                            resultData['results'][0]['phone_number']);

                                    }

                                }
                            });



                        });


                        $('#answer_architect').on('change', function() {


                            $.ajax({
                                type: 'GET',
                                url: ajaxURLSearchArchitect + "?user_id=" + $(this).val(),
                                success: function(resultData) {

                                    if (resultData['results'].length > 0) {


                                        $("#answer_architect_phone_number").val(resultData[
                                            'results'][0]['phone_number']);

                                    }

                                }
                            });



                        });


                        if (typeof resultData['architect'] !== "undefined" && typeof resultData['architect'][
                                'id'
                            ] !== "undefined") {
                            $("#answer_architect").empty().trigger('change');
                            var newOption = new Option(resultData['architect']['text'], resultData['architect'][
                                'id'
                            ], false, false);
                            $('#answer_architect').append(newOption).trigger('change');

                        }

                        if (typeof resultData['electrician'] !== "undefined" && typeof resultData['electrician']
                            ['id'] !== "undefined") {
                            $("#answer_electrician").empty().trigger('change');
                            var newOption = new Option(resultData['electrician']['text'], resultData[
                                'electrician']['id'], false, false);
                            $('#answer_electrician').append(newOption).trigger('change');

                        }






                        $(".select2-apply").select2({
                            minimumResultsForSearch: Infinity,
                            dropdownParent: $("#modalStatusChange .modal-body")
                        });

                        $(".select2-multi-apply").select2({
                            minimumResultsForSearch: Infinity,
                            allowClear: true,
                            dropdownParent: $("#modalStatusChange .modal-body")
                        });


                        $("#answer_follow_up_time").select2({
                            dropdownParent: $("#modalStatusChange .modal-content")
                        });

                        if (resultData['inquiry']['quotation'] != "") {

                            // $("#inquiry_questions_7").select2("val", ""+resultData['inquiry']['stage_of_site']);

                            $("#answer-value-1").html(resultData['inquiry']['quotation']);
                            $("#inquiry_questions_1").removeAttr('required');
                            $("#row_answer_1 .highlighter-rouge").hide();

                        }

                        if (resultData['inquiry']['quotation_amount'] != "") {

                            $("#inquiry_questions_2").val(resultData['inquiry']['quotation_amount']);

                        }

                        if (resultData['inquiry']['billing_invoice'] != "") {

                            // $("#inquiry_questions_7").select2("val", ""+resultData['inquiry']['stage_of_site']);


                            $("#inquiry_questions_11").removeAttr('required');
                            $("#row_answer_11 .highlighter-rouge").hide();

                        }

                        if (resultData['inquiry']['billing_amount'] != "") {

                            $("#inquiry_questions_12").val(resultData['inquiry']['billing_amount']);


                        }









                        if (resultData['inquiry']['architect_name'] != "") {

                            // $("#inquiry_questions_3").val(resultData['inquiry']['architect_name']);

                        }

                        if (resultData['inquiry']['architect_phone_number'] != "") {

                            // $("#inquiry_questions_4").val(resultData['inquiry']['architect_phone_number']);

                        }

                        if (resultData['inquiry']['electrician_name'] != "") {

                            // $("#inquiry_questions_5").val(resultData['inquiry']['electrician_name']);

                        }
                        if (resultData['inquiry']['electrician_phone_number'] != "") {

                            //$("#inquiry_questions_6").val(resultData['inquiry']['electrician_phone_number']);

                        }

                        if (resultData['inquiry']['stage_of_site'] != "") {

                            // $("#inquiry_questions_7").select2("val", ""+resultData['inquiry']['stage_of_site']);
                            $("#inquiry_questions_7").val("" + resultData['inquiry']['stage_of_site']);
                            $("#inquiry_questions_7").trigger('change');

                        }

                        if (resultData['inquiry']['site_photos'] != "") {

                            // $("#inquiry_questions_7").select2("val", ""+resultData['inquiry']['stage_of_site']);

                            $("#answer-value-8").html(resultData['inquiry']['site_photos']);
                            $("#inquiry_questions_8").removeAttr('required');
                            $("#row_answer_8 .highlighter-rouge").hide();

                        }



                        if (resultData['inquiry']['required_for_property'] != "") {

                            $("#inquiry_questions_9").val("" + resultData['inquiry']['required_for_property']);
                            $("#inquiry_questions_9").trigger('change');

                        }
                        if (resultData['inquiry']['changes_of_closing_order'] != "") {

                            $("#inquiry_questions_10").val("" + resultData['inquiry'][
                                'changes_of_closing_order'
                            ]);
                            $("#inquiry_questions_10").trigger('change');

                        }




                    } else {
                        toastr["error"](resultData['msg']);
                    }
                }
            });


        }

        function changeStageOfSite(id) {

            var stageOfSiteVal = $("#stage_of_site_" + id).val();

            $.ajax({
                type: 'GET',
                url: ajaxSaveStageOfSite + "?inquiry_id=" + id + "&stage_of_site=" + stageOfSiteVal,
                success: function(resultData) {
                    if (resultData['status'] == 1) {
                        toastr["success"](resultData['msg']);
                    } else {
                        if (typeof resultData['data'] !== "undefined") {

                            var size = Object.keys(resultData['data']).length;
                            if (size > 0) {

                                for (var [key, value] of Object.entries(resultData['data'])) {

                                    toastr["error"](value);
                                }

                            }

                        } else {
                            toastr["error"](resultData['msg']);
                        }
                    }

                }
            });

        }

        function changeFollowupType(id) {

            var followupType = $("#follow_type_id_" + id).val();

            $.ajax({
                type: 'GET',
                url: ajaxSaveFollowUpType + "?inquiry_id=" + id + "&follow_up_type=" + followupType,
                success: function(resultData) {
                    if (resultData['status'] == 1) {
                        toastr["success"](resultData['msg']);
                    } else {
                        if (typeof resultData['data'] !== "undefined") {

                            var size = Object.keys(resultData['data']).length;
                            if (size > 0) {

                                for (var [key, value] of Object.entries(resultData['data'])) {

                                    toastr["error"](value);
                                }

                            }

                        } else {
                            toastr["error"](resultData['msg']);
                        }
                    }

                }
            });

        }


        function editQuotation(id) {

            $('#formQuotation').trigger("reset");

            $("#modalQuotation").modal('show');
            $.ajax({
                type: 'GET',
                url: ajaxURLInquiryDetail + "?inquiry_id=" + id + "&ui_type=inquiry_detail",
                success: function(resultData) {
                    if (resultData['status'] == 1) {

                        $("#modalQuotationLabel").html("Edit Quotation Inquiry #" + resultData['detail']['id']);
                        $(".loadingcls").hide();
                        $("#modalInquiry .modal-footer").show();

                        $("#inquiry_quotation_amount").val(resultData['detail']['quotation_amount']);
                        $("#quotation_inquiry_id").val(resultData['detail']['id']);

                    } else {

                        toastr["error"](resultData['msg']);

                    }
                }
            });


        }


        function editBillingInvoice(id) {
            //claimed24HoursIn

            $('#formBilling').trigger("reset");

            $("#modalBilling").modal('show');
            $.ajax({
                type: 'GET',
                url: ajaxURLInquiryDetail + "?inquiry_id=" + id + "&ui_type=inquiry_detail",
                success: function(resultData) {

                    if (resultData['status'] == 1) {

                        $("#modalBillingLabel").html("Edit Billing Invoice - Inquiry #" + resultData['detail'][
                            'id'
                        ]);
                        $(".loadingcls").hide();
                        $("#modalBilling .modal-footer").show();
                        $("#billing_inquiry_id").val(resultData['detail']['id']);
                        if (isAdminOrCompanyAdmin == 1) {

                            $("#inquiry_billing_amount").val(resultData['detail']['billing_amount']);

                            //     if (claimed24HoursIn == 1) {
                            //         $("#div_inquiry_billing_amount").show();
                            //         $("#inquiry_billing_amount").val(resultData['detail']['billing_amount']);
                            //     } else {
                            //         $("#div_inquiry_billing_amount").hide();
                            //     }
                        }


                    } else {

                        toastr["error"](resultData['msg']);

                    }
                }
            });


        }

        function openReplyBox(id) {



            $("#reply-box-" + id).show(300);

        }

        function openBillingInvoiceModal(inquiryId, invoiceURLs) {

            if (invoiceURLs != "") {

                var invoiceArray = invoiceURLs.split(",");
            } else {
                var invoiceArray = [];
            }


            var modalBodyForInvoice = "<table class='table table-bordered' ><tbody>";
            for (var i = 0; i < invoiceArray.length; i++) {
                modalBodyForInvoice += "<tr class='text-center' id='inquiry-invoice-index-id-" + i + "' >";
                modalBodyForInvoice += "<td>";
                modalBodyForInvoice += "<a class='btn btn-primary btn-sm waves-effect waves-light' href='" +
                    getSpaceFilePath(invoiceArray[i]) + " ' target='_blank' > Invoice " + (i + 1) + " </a>";
                modalBodyForInvoice += "</td>";
                if (isAdminOrCompanyAdmin == 1) {

                    modalBodyForInvoice += "<td>";
                    modalBodyForInvoice +=
                        "<a class='btn btn-danger btn-sm waves-effect waves-light' onclick='deleteInvoiceByIndex(" +
                        inquiryId + "," + i +
                        ")'  href='javascript:void(0)' > <i data-bs-toggle='tooltip' class='bx bx bx-trash bx-sm extrasmallfont' data-bs-original-title='Delete' aria-label ></i>  </a>";
                    modalBodyForInvoice += "</td>";





                    //  inquiryId

                }


                modalBodyForInvoice += "</tr>";
            }



            modalBodyForInvoice += "</tbody></table>";

            $("#modalBillingInvoice").modal('show');
            $("#modalBillingInvoiceBody").html(modalBodyForInvoice)

        }


        function deleteInvoiceByIndex(inquiryId, invoiceIndex) {
            // console.log(inquiryId);
            // console.log(invoiceIndex);



            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: !0,
                confirmButtonText: "Yes, delete it !",
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
                            url: ajaxURLInquiryInvoiceDelete + "?inquiry_id=" + inquiryId +
                                "&invoice_index=" + invoiceIndex,
                            success: function(resultData) {

                                if (resultData['status'] == 1) {

                                    // $("#inquiry-invoice-index-id-"+invoiceIndex).remove();
                                    openBillingInvoiceModal(inquiryId, resultData[
                                        'billing_invoice']);

                                    table.ajax.reload(null, false);
                                    t()



                                }




                            }
                        });



                    })
                },
            }).then(function(t) {

                if (t.value === true) {



                    Swal.fire({
                        title: "Successfully deleted invoice",
                        text: "Your record has been deleted.",
                        icon: "success"
                    });


                }

            });



        }

        function editView(id) {
            resetInputForm();
            $.ajax({
                type: 'GET',
                url: ajaxURLInquiryDetail + "?inquiry_id=" + id + "&ui_type=inquiry_detail",
                success: function(resultData) {




                    if (resultData['status'] == 1) {

                        $("#modalInquiry").modal('show');






                        ///START - SIMILAR LIKE ADD BTN FLOW


                        $("#modalInquiryLabel").html("Edit Inquiry #" + resultData['detail']['id'] + " (" + $(
                            "#inquiry-name-" + resultData['detail']['id']).html() + ")");
                        $(".loadingcls").hide();
                        $("#modalInquiry .modal-footer").show();
                        $(".row-more-detail").hide();
                        $("#lessDetailBtn").hide();
                        $("#moreDetailBtn").show()
                        $("#div_source_text").hide();
                        $("#div_source_user").hide();


                        ///END - SIMILAR LIKE ADD BTN FLOW

                        // $("#div_source").hide();
                        $("#inquiry_source_type").val("" + resultData['detail']['source_type']);
                        $("#inquiry_source_type").trigger('change');

                        var pieces_inquiry_source_type = resultData['detail']['source_type'].split("-");

                        if (pieces_inquiry_source_type[0] == "user") {

                            if (pieces_inquiry_source_type[1] == 4) {
                                setTimeout(function() {
                                    var newOption = new Option(resultData['detail'][
                                        'source_type_value'
                                    ], resultData['detail'][
                                        'source_type_value'
                                    ], false, false);
                                    $('#inquiry_source_user').append(newOption).trigger('change');
                                }, 200);
                            } else {
                                if (resultData['detail']['source'] !== undefined && resultData['detail'][
                                        'source'
                                    ][
                                        'id'
                                    ] !== undefined) {


                                    setTimeout(function() {

                                        $("#inquiry_source_user").empty().trigger('change');

                                        var newOption = new Option(resultData['detail']['source'][
                                                'text'
                                            ],
                                            resultData['detail']['source']['id'], false, false);
                                        $('#inquiry_source_user').append(newOption).trigger('change');

                                    }, 200);

                                }
                            }



                        } else if (pieces_inquiry_source_type[0] == "exhibition") {

                            if (resultData['detail']['source'] !== undefined && resultData['detail']['source'][
                                    'id'
                                ] !== undefined) {


                                setTimeout(function() {

                                    $("#inquiry_source_exhibition").empty().trigger('change');

                                    var newOption = new Option(resultData['detail']['source']['text'],
                                        resultData['detail']['source']['id'], false, false);
                                    $('#inquiry_source_exhibition').append(newOption).trigger('change');

                                }, 200);

                            }


                        } else if (pieces_inquiry_source_type[0] == "textrequired" ||
                            pieces_inquiry_source_type[0] == "textnotrequired") {

                            setTimeout(function() {
                                $("#inquiry_source_text").val(resultData['detail'][
                                    'source_type_value'
                                ]);

                            }, 200);




                        }

                        if (isChannelPartner == 0 && isArchitect == 0 && isElectrician == 0) {


                        } else {
                            $("#div_source").hide();

                            $("#div_source_text_1").hide();
                            $("#div_source_user_1").hide();
                            $("#div_source_text_2").hide();
                            $("#div_source_user_2").hide();
                            $("#div_source_text_3").hide();
                            $("#div_source_user_3").hide();
                            $("#div_source_text_4").hide();
                            $("#div_source_user_4").hide();
                            $("#row_architect").hide();
                            $("#row_electician").hide();


                        }

                        $("#div_source_1").hide();
                        $("#div_source_2").hide();
                        $("#div_source_3").hide();
                        $("#div_source_4").hide();






                        if (resultData['detail']['source_type_1'] != "") {


                            $("#inquiry_source_type_1").val("" + resultData['detail']['source_type_1']);

                            $("#inquiry_source_type_1").trigger('change');

                            var pieces_inquiry_source_type_1 = resultData['detail']['source_type_1'].split("-");

                            if (pieces_inquiry_source_type_1[0] == "user") {

                                if (pieces_inquiry_source_type_1[1] == 4) {
                                    setTimeout(function() {
                                        var newOption = new Option(resultData['detail'][
                                                'source_type_value_1'
                                            ], resultData['detail']['source_type_value_1'], false,
                                            false);
                                        $('#inquiry_source_user_1').append(newOption).trigger('change');
                                    }, 200);
                                } else {

                                    if (resultData['detail']['source_1'] !== undefined && resultData['detail'][
                                            'source_1'
                                        ]['id'] !== undefined) {


                                        setTimeout(function() {
                                            $("#addMoreSource").trigger('click');


                                            $("#inquiry_source_user_1").empty().trigger('change');

                                            var newOption = new Option(resultData['detail']['source_1'][
                                                    'text'
                                                ], resultData['detail']['source_1']['id'], false,
                                                false);
                                            $('#inquiry_source_user_1').append(newOption).trigger(
                                                'change');

                                        }, 200);

                                    }
                                }




                            } else if (pieces_inquiry_source_type_1[0] == "textrequired" ||
                                pieces_inquiry_source_type_1[0] == "textnotrequired") {

                                setTimeout(function() {
                                    $("#addMoreSource").trigger('click');
                                    $("#inquiry_source_text_1").val(resultData['detail'][
                                        'source_type_value_1'
                                    ]);

                                }, 200);




                            } else if (pieces_inquiry_source_type_1[0] == "fix") {
                                $("#addMoreSource").trigger('click');
                            }
                        }


                        if (resultData['detail']['source_type_2'] != "") {




                            $("#inquiry_source_type_2").val("" + resultData['detail']['source_type_2']);

                            $("#inquiry_source_type_2").trigger('change');

                            var pieces_inquiry_source_type_2 = resultData['detail']['source_type_2'].split("-");

                            if (pieces_inquiry_source_type_2[0] == "user") {

                                if (pieces_inquiry_source_type_2[1] == 4) {
                                    setTimeout(function() {
                                        var newOption = new Option(resultData['detail'][
                                                'source_type_value_2'
                                            ], resultData['detail']['source_type_value_2'], false,
                                            false);
                                        $('#inquiry_source_user_2').append(newOption).trigger('change');
                                    }, 200);
                                } else {
                                    if (resultData['detail']['source_2'] !== undefined && resultData['detail'][
                                            'source_2'
                                        ]['id'] !== undefined) {


                                        setTimeout(function() {




                                            $("#inquiry_source_user_2").empty().trigger('change');
                                            $("#addMoreSource").trigger('click');

                                            var newOption = new Option(resultData['detail']['source_2'][
                                                    'text'
                                                ], resultData['detail']['source_2']['id'], false,
                                                false);
                                            $('#inquiry_source_user_2').append(newOption).trigger(
                                                'change');

                                        }, 200);

                                    }
                                }




                            } else if (pieces_inquiry_source_type_2[0] == "textrequired" ||
                                pieces_inquiry_source_type_2[0] == "textnotrequired") {

                                setTimeout(function() {
                                    $("#addMoreSource").trigger('click');
                                    $("#inquiry_source_text_2").val(resultData['detail'][
                                        'source_type_value_2'
                                    ]);

                                }, 200);




                            } else if (pieces_inquiry_source_type_2[0] == "fix") {
                                $("#addMoreSource").trigger('click');
                            }
                        }


                        if (resultData['detail']['source_type_3'] != "") {


                            $("#inquiry_source_type_3").val("" + resultData['detail']['source_type_3']);

                            $("#inquiry_source_type_3").trigger('change');

                            var pieces_inquiry_source_type_3 = resultData['detail']['source_type_3'].split("-");

                            if (pieces_inquiry_source_type_3[0] == "user") {

                                if (pieces_inquiry_source_type_3[1] == 4) {
                                    setTimeout(function() {
                                        var newOption = new Option(resultData['detail'][
                                                'source_type_value_3'
                                            ], resultData['detail']['source_type_value_3'], false,
                                            false);
                                        $('#inquiry_source_user_3').append(newOption).trigger('change');
                                    }, 200);
                                } else {
                                    if (resultData['detail']['source_3'] !== undefined && resultData['detail'][
                                            'source_3'
                                        ]['id'] !== undefined) {


                                        setTimeout(function() {


                                            $("#inquiry_source_user_3").empty().trigger('change');
                                            $("#addMoreSource").trigger('click');

                                            var newOption = new Option(resultData['detail']['source_3'][
                                                    'text'
                                                ], resultData['detail']['source_3']['id'], false,
                                                false);
                                            $('#inquiry_source_user_3').append(newOption).trigger(
                                                'change');

                                        }, 200);

                                    }
                                }




                            } else if (pieces_inquiry_source_type_3[0] == "textrequired" ||
                                pieces_inquiry_source_type_3[0] == "textnotrequired") {

                                setTimeout(function() {
                                    $("#addMoreSource").trigger('click');
                                    $("#inquiry_source_text_3").val(resultData['detail'][
                                        'source_type_value_3'
                                    ]);

                                }, 200);




                            } else if (pieces_inquiry_source_type_3[0] == "fix") {
                                $("#addMoreSource").trigger('click');
                            }
                        }


                        if (resultData['detail']['source_type_4'] != "") {


                            $("#inquiry_source_type_4").val("" + resultData['detail']['source_type_4']);

                            $("#inquiry_source_type_4").trigger('change');

                            var pieces_inquiry_source_type_4 = resultData['detail']['source_type_4'].split("-");

                            if (pieces_inquiry_source_type_4[0] == "user") {

                                if (pieces_inquiry_source_type_4[1] == 4) {
                                    setTimeout(function() {
                                        var newOption = new Option(resultData['detail'][
                                                'source_type_value_4'
                                            ], resultData['detail']['source_type_value_4'], false,
                                            false);
                                        $('#inquiry_source_user_4').append(newOption).trigger('change');
                                    }, 200);
                                } else {
                                    if (resultData['detail']['source_4'] !== undefined && resultData['detail'][
                                            'source_4'
                                        ]['id'] !== undefined) {


                                        setTimeout(function() {


                                            $("#inquiry_source_user_4").empty().trigger('change');
                                            $("#addMoreSource").trigger('click');

                                            var newOption = new Option(resultData['detail']['source_4'][
                                                    'text'
                                                ], resultData['detail']['source_4']['id'], false,
                                                false);
                                            $('#inquiry_source_user_4').append(newOption).trigger(
                                                'change');

                                        }, 200);

                                    }
                                }




                            } else if (pieces_inquiry_source_type_4[0] == "textrequired" ||
                                pieces_inquiry_source_type_4[0] == "textnotrequired") {

                                setTimeout(function() {
                                    $("#inquiry_source_text_4").val(resultData['detail'][
                                        'source_type_value_4'
                                    ]);
                                    $("#addMoreSource").trigger('click');

                                }, 200);




                            } else if (pieces_inquiry_source_type_4[0] == "fix") {

                                $("#addMoreSource").trigger('click');
                            }
                        }




                        $("#inquiry_first_name").val(resultData['detail']['first_name']);
                        $("#inquiry_last_name").val(resultData['detail']['last_name']);
                        $("#inquiry_phone_number").val(resultData['detail']['phone_number']);
                        $("#inquiry_phone_number2").val(resultData['detail']['phone_number2']);
                        $("#inquiry_pincode").val(resultData['detail']['pincode']);
                        $("#inquiry_house_no").val(resultData['detail']['house_no']);
                        $("#inquiry_society_name").val(resultData['detail']['society_name']);
                        $("#inquiry_area").val(resultData['detail']['area']);


                        if (isChannelPartner == 0 && typeof resultData['detail']['architect'] !== "undefined" &&
                            typeof resultData['detail']['architect']['id'] !== "undefined") {

                            setTimeout(function() {
                                $("#inquiry_architect").empty().trigger('change');
                                var newOption = new Option(resultData['detail']['architect']['text'],
                                    resultData['detail']['architect']['id'], false, false);
                                $('#inquiry_architect').append(newOption).trigger('change');
                            }, 1000);


                        } else {
                            setTimeout(function() {
                                $("#inquiry_architect").empty().trigger('change');
                                $("#inquiry_architect_phone_number").val("");

                            }, 1000);

                        }

                        if (isChannelPartner == 0 && typeof resultData['detail']['electrician'] !==
                            "undefined" && typeof resultData['detail']['electrician']['id'] !== "undefined") {

                            setTimeout(function() {
                                $("#inquiry_electrician").empty().trigger('change');
                                var newOption = new Option(resultData['detail']['electrician']['text'],
                                    resultData['detail']['electrician']['id'], false, false);
                                $('#inquiry_electrician').append(newOption).trigger('change');
                            }, 1000);

                        } else {

                            setTimeout(function() {

                                $("#inquiry_electrician").empty().trigger('change');
                                $("#inquiry_electrician_phone_number").val("");


                            }, 1000);
                        }


                        // $("#inquiry_architect_name").val(resultData['detail']['architect_name']);
                        // $("#inquiry_architect_phone_number").val(resultData['detail']['architect_phone_number']);

                        // $("#inquiry_electrician_name").val(resultData['detail']['electrician_name']);
                        // $("#inquiry_electrician_phone_number").val(resultData['detail']['electrician_phone_number']);

                        $("#new_inquiry_id").val(resultData['detail']['id']);

                        if (typeof resultData['detail']['city']['id'] !== "undefined") {
                            $("#inquiry_city_id").empty().trigger('change');
                            var newOption = new Option(resultData['detail']['city']['text'], resultData[
                                'detail']['city']['id'], false, false);
                            $('#inquiry_city_id').append(newOption).trigger('change');

                        }

                        if (typeof resultData['detail']['assigned_to']['id'] !== "undefined") {
                            $("#inquiry_assigned_to").empty().trigger('change');
                            var newOption = new Option(resultData['detail']['assigned_to']['text'], resultData[
                                'detail']['assigned_to']['id'], false, false);
                            $('#inquiry_assigned_to').append(newOption).trigger('change');

                        }
                        if (resultData['detail']['required_for_property'] != "") {

                            $("#pre_inquiry_questions_9").val("" + resultData['detail'][
                                'required_for_property'
                            ]);
                            $("#pre_inquiry_questions_9").trigger('change');



                        }

                        if (resultData['detail']['stage_of_site'] != "") {

                            $("#pre_inquiry_questions_7").val("" + resultData['detail']['stage_of_site']);
                            $("#pre_inquiry_questions_7").trigger('change');



                        }

                        if (resultData['detail']['changes_of_closing_order'] != "") {

                            $("#pre_inquiry_questions_10").val("" + resultData['detail'][
                                'changes_of_closing_order'
                            ]);
                            $("#pre_inquiry_questions_10").trigger('change');



                        }

                        if (resultData['detail']['follow_up_type'] != "") {

                            $("#inquiry_follow_up_type").val("" + resultData['detail']['follow_up_type']);
                            $("#inquiry_follow_up_type").trigger('change');



                        }

                        if (resultData['detail']['follow_up_date_time'] != "") {
                            // $("#inquiry_follow_up_date_time").val(""+resultData['detail']['follow_up_date_time']);
                            $("#inquiry_follow_up_date").val(resultData['detail']['follow_up_date']);
                            $("#inquiry_follow_up_time").val(resultData['detail']['follow_up_time']);
                            $("#inquiry_follow_up_date").trigger('change');
                            $("#inquiry_follow_up_time").trigger('change');

                        }




                    } else {
                        if (typeof resultData['data'] !== "undefined") {

                            var size = Object.keys(resultData['data']).length;
                            if (size > 0) {

                                for (var [key, value] of Object.entries(resultData['data'])) {

                                    toastr["error"](value);
                                }

                            }

                        } else {
                            toastr["error"](resultData['msg']);
                        }

                    }
                }


            });


        }



        $('#datatable').on('length.dt', function(e, settings, len) {

            setCookie('inquiryPageLength', len, 100);


        });


        $('#inquiry_filter_following_date_time').on('change', function() {
            table.ajax.reload();
        });

        $('#inquiry_filter_search_type').on('change', function() {
            table.ajax.reload();
        });
        $('#inquiry_filter_material_sent_type').on('change', function() {
            table.ajax.reload();
        });
        $('#inquiry_filter_closing').on('change', function() {
            table.ajax.reload();
        });
        $('#inquiry_filter_sure_not_sure').on('change', function() {
            table.ajax.reload();
        });


        $('#inquiry_quotation_filter').on('keyup', function() {
            table.ajax.reload();
        });


        $('#inquiry_filter_search_value').on('keyup', function() {
            table.ajax.reload();
        });

        $('#inquiry_filter_stage_of_site').on('change', function() {
            table.ajax.reload();
        });

        $(document).ready(function() {

            if (openInquiryId != 0) {

                $("#inquiry_filter_search_type").val(2);
                $("#inquiry_filter_search_value").val(openInquiryId);
                table.ajax.reload();

            }


        });



        $('#inquiry_phone_number').on('change', function() {

            $.ajax({
                type: 'POST',
                url: ajaxURLInquiryPhoneNumber,
                data: {
                    "_token": csrfToken,
                    "inquiry_phone_number": function() {
                        return $("#inquiry_phone_number").val();
                    },


                },
                success: function(resultData) {


                    if (resultData['status'] == 1) {



                    } else {

                        toastr["error"](resultData['msg']);
                    }
                }
            });

        });



        var currentURL = window.location.href;
        var loadedURLLink = $('.userscomman a[href="' + currentURL + '"]');
        $(loadedURLLink).removeClass('status-tab-active');
        $(loadedURLLink).addClass('status-tab-active');
    </script>
@endsection
