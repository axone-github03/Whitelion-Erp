@extends('layouts.main')
@section('title', $data['title'])
@section('content')
    <style>
        .page-content {
            padding: calc(70px + 24px) calc(24px / 2) 0px calc(24px / 2) !important;
        }

        .lead-status-btn {
            background: #808080;
            color: white !important;
            margin-top: 5px;
            margin-bottom: 5px
        }

        .lead-status-btn-white {
            background: #ffffff;
            color: rgb(0, 0, 0) !important;
            margin-top: 5px;
            margin-bottom: 5px
        }

        .btn-arrow-right,
        .btn-arrow-left {
            position: relative;
            padding-left: 18px;
            padding-right: 18px;
        }

        .btn-arrow-right {
            padding-left: 23px;
            margin-right: 0px;
        }

        .btn-arrow-left {
            padding-right: 36px;
        }

        .btn-arrow-right:before,
        .btn-arrow-right:after,
        .btn-arrow-left:before,
        .btn-arrow-left:after {
            /* make two squares (before and after), looking similar to the button */
            content: "";
            position: absolute;
            top: 3px;
            /* move it down because of rounded corners */
            width: 19px;
            /* same as height */
            height: 19px;
            /* button_outer_height / sqrt(2) */
            background: inherit;
            /* use parent background */
            border: inherit;
            /* use parent border */
            border-left-color: transparent;
            /* hide left border */
            border-bottom-color: transparent;
            /* hide bottom border */
            border-radius: 0px 4px 0px 0px;
            /* round arrow corner, the shorthand property doesn't accept "inherit" so it is set to 4px */
            -webkit-border-radius: 0px 4px 0px 0px;
            -moz-border-radius: 0px 4px 0px 0px;
        }

        .btn-arrow-right:before,
        .btn-arrow-right:after {
            transform: rotate(45deg);
            /* rotate right arrow squares 45 deg to point right */
            -webkit-transform: rotate(45deg);
            -moz-transform: rotate(45deg);
            -o-transform: rotate(45deg);
            -ms-transform: rotate(45deg);
        }

        .btn-arrow-left:before,
        .btn-arrow-left:after {
            transform: rotate(225deg);
            /* rotate left arrow squares 225 deg to point left */
            -webkit-transform: rotate(225deg);
            -moz-transform: rotate(225deg);
            -o-transform: rotate(225deg);
            -ms-transform: rotate(225deg);
        }

        .btn-arrow-right:before,
        .btn-arrow-left:before {
            /* align the "before" square to the left */
            left: -9px;
        }

        .btn-arrow-right:after,
        .btn-arrow-left:after {
            /* align the "after" square to the right */
            right: -9px;
        }

        .btn-arrow-right:after,
        .btn-arrow-left:before {
            /* bring arrow pointers to front */
            z-index: 1;
        }

        .btn-arrow-right:before,
        .btn-arrow-left:after {
            /* hide arrow tails background */
            background-color: rgb(247, 247, 250);
        }

        .lead-detail .btn-arrow-right:before,
        .btn-arrow-left:after {
            /* hide arrow tails background */
            background-color: white;
        }

        .lead-search-form {
            width: 100%;
            display: inline-block !important;
            padding: 0;
            margin-top: 5px;
        }

        .lead-search-form .form-control {
            background: white;
            border-radius: 2px;
            border: 1px solid gainsboro;
        }

        .col-form-label {
            /* padding: 2px 11px; */
        }

        /* .lead-detail .form-control, */
        /* .input-group-text { */
        /* padding: 2px 11px; */
        /* border: none;
                                                                                                                                                                                                                                                                                    } */

        @media (min-width: 1200px) {
            .chat-leftsidebar {
                min-width: 330px;
            }
        }

        .card-header {
            /* background: #000000c2 !important; */
            border-radius: 5px;
            font-weight: 300;
            color: black;
            font-size: 12px;
            font-weight: 400;
        }

        .btn-header-right {
            margin-top: -3px;
        }

        .nav-pills>li>a,
        .nav-tabs>li>a,
        .nav-tabs>li>a span {
            font-weight: 400;
        }

        .active_lead {
            background-color: rgb(141, 226, 255);
            border-color: transparent;
            -webkit-box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, .03);
            box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, .03);
        }

        .static_hover tr:hover {
            background-color: rgb(141, 226, 255);
            border-color: transparent;
            -webkit-box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, .03);
            box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, .03);
        }

        .reminder_checkbox {
            width: 15px;
        }

        .lead-detail,
        .lead-list {
            /* box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px; */
            box-shadow: 0px 3px 3px 0px rgba(0, 0, 0, 0.05);
        }

        .status-active-class {
            background-color: #556ee6 !important;
            color: #ffffff !important
        }

        .status-active-class:before {
            border-left-color: #556ee6 !important;
            color: #fff;
        }

        .next-status-active-class {
            background-color: #556ee6;
            color: #ffffff !important
        }

        .border-bottom {
            /* border-bottom: 1px solid #d1d1d1 !important; */
        }

        .border-none {
            border: none !important;
        }

        /* #modalCall .select2-selection.select2-selection--single {
                                                                                                                                                                                                                                                                                        border: none !important;
                                                                                                                                                                                                                                                                                    }

                                                                                                                                                                                                                                                                                    #modalMeeting .select2-selection.select2-selection--single {
                                                                                                                                                                                                                                                                                        border: none !important;
                                                                                                                                                                                                                                                                                    }

                                                                                                                                                                                                                                                                                    #modalMeeting .select2-selection.select2-selection--multiple {
                                                                                                                                                                                                                                                                                        border: none !important;
                                                                                                                                                                                                                                                                                    }

                                                                                                                                                                                                                                                                                    #modalTask .select2-selection.select2-selection--single {
                                                                                                                                                                                                                                                                                        border: none !important;
                                                                                                                                                                                                                                                                                    } */

        .bx.bx-check-circle.text-success {
            cursor: pointer;
        }

        .select2-search__field {
            width: 100% !important;
        }

        span.closing-badge,
        span.closing-badge1 {
            border-radius: 7px !important;
            background: rgb(83, 89, 247);
            height: 19px;
            /* margin-top: 6px; */
            color: white;
            width: 25px;
            text-align: center;
            margin-right: 1px;
            padding: 0 4px;
            margin-left: 3px;
        }



        div.div_tip,
        div.div_tip1 {
            min-width: 60px;
            min-height: 60px;
            display: none;
            background: #bbbefcf0;
            position: absolute;
            /* z-index: -1; */
            border-radius: 5px;
            -moz-border-radius: 5px;
            box-shadow: 0px 1px 2px #888888;
            -moz-box-shadow: 0px 1px 2px #888888;
        }

        div.div_tip:hover,
        div.div_tip1:hover {
            /* z-index: 1; */
            display: block;
        }

        .closing-badge:hover+.div_tip,
        .closing-badge1:hover+.div_tip1 {
            z-index: 1;
            /* visibility: visible; */
        }

        div.div_tip .tip_arrow,
        div.div_tip1 .tip_arrow1 {
            position: absolute;
            /*top: 100%;*/
            /*left: 50%;*/
            border: solid transparent;
            height: 0;
            width: 0;
            pointer-events: none;
        }

        div.div_tip .tip_arrow,
        div.div_tip1 .tip_arrow1 {
            /*border-color: rgba(62, 83, 97, 0);*/
            /*border-top-color: #3e5361;*/
            border-width: 10px;
            /*margin-left: -10px; */
        }

        span.closing-badge3 {
            border-radius: 0.25rem !important;
            background: rgb(239 242 247);
            padding: 5px 4px;
        }

        span.closing-badge4 {
            border-radius: 0.25rem !important;
            background: rgb(239 242 247);
            padding: 5px 4px;
        }



        div.div_tip3,
        div.div_tip4 {
            /* min-width: 100%; */
            display: none;
            background: #bbbefcf0;
            position: absolute;
            /* z-index: -1; */
            border-radius: 5px;
            -moz-border-radius: 5px;
            box-shadow: 0px 1px 2px #888888;
            -moz-box-shadow: 0px 1px 2px #888888;
        }

        div.div_tip3:hover,
        div.div_tip4:hover {
            /* z-index: 1; */
            display: block;
        }

        .closing-badge3:hover+.div_tip3,
        .closing-badge4:hover+.div_tip4 {
            display: block !important;
            z-index: 999;
        }

        div.div_tip3 .tip_arrow3,
        div.div_tip4 .tip_arrow4 {
            position: absolute;
            /*top: 100%;*/
            /*left: 50%;*/
            border: solid transparent;
            height: 0;
            width: 0;
            pointer-events: none;
        }

        div.div_tip3 .tip_arrow3,
        div.div_tip4 .tip_arrow4 {
            /*border-color: rgba(62, 83, 97, 0);*/
            /*border-top-color: #3e5361;*/
            border-width: 10px;
            /*margin-left: -10px; */
        }

        .lds-spinner {
            display: inline-block;
            position: relative;
            width: 34px;
            height: 15px;
        }

        .lds-spinner div {
            transform-origin: 31px 10px;
            animation: lds-spinner 1.2s linear infinite;
        }

        .lds-spinner div:after {
            content: " ";
            display: block;
            position: absolute;
            top: 0px;
            left: 30px;
            width: 2px;
            height: 7px;
            border-radius: 20%;
            background: #000;
        }

        .lds-spinner div:nth-child(1) {
            transform: rotate(0deg);
            animation-delay: -1.1s;
        }

        .lds-spinner div:nth-child(2) {
            transform: rotate(30deg);
            animation-delay: -1s;
        }

        .lds-spinner div:nth-child(3) {
            transform: rotate(60deg);
            animation-delay: -0.9s;
        }

        .lds-spinner div:nth-child(4) {
            transform: rotate(90deg);
            animation-delay: -0.8s;
        }

        .lds-spinner div:nth-child(5) {
            transform: rotate(120deg);
            animation-delay: -0.7s;
        }

        .lds-spinner div:nth-child(6) {
            transform: rotate(150deg);
            animation-delay: -0.6s;
        }

        .lds-spinner div:nth-child(7) {
            transform: rotate(180deg);
            animation-delay: -0.5s;
        }

        .lds-spinner div:nth-child(8) {
            transform: rotate(210deg);
            animation-delay: -0.4s;
        }

        .lds-spinner div:nth-child(9) {
            transform: rotate(240deg);
            animation-delay: -0.3s;
        }

        .lds-spinner div:nth-child(10) {
            transform: rotate(270deg);
            animation-delay: -0.2s;
        }

        .lds-spinner div:nth-child(11) {
            transform: rotate(300deg);
            animation-delay: -0.1s;
        }

        .lds-spinner div:nth-child(12) {
            transform: rotate(330deg);
            animation-delay: 0s;
        }

        @keyframes lds-spinner {
            0% {
                opacity: 1;
            }

            100% {
                opacity: 0;
            }
        }

        .dropdown-menu-advancefilter {
            width: 700px !important;
        }

        input[type="radio"] {
            appearance: none;
            border: 1px solid #d3d3d3;
            width: 20px;
            height: 20px;
            content: none;
            outline: none;
            margin: 0;
            /* box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px; */
        }

        input[type="radio"]:checked {
            appearance: none;
            outline: none;
            padding: 0;
            content: none;
            border: none;
        }

        input[type="radio"]:checked::before {
            position: absolute;
            color: green !important;
            content: "\00A0\2713\00A0" !important;
            /* border: 1px solid #d3d3d3; */
            font-weight: bolder;
            font-size: 21px;
            /* width: 20px;
                                                                                                                                                                                                                                                                                                                                    height: 20px; */
        }

        .star-radio {
            display: inline-block;
            margin-right: 5px;
            margin-bottom: 0px;
        }

        .star-radio input[type="radio"] {
            display: none;
        }

        .star-radio .star {
            display: inline-block;
            width: 16px;
            height: 16px;
            background-image: url({{ asset('assets/images/star.png') }});
            /* Replace with your star image */
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            /* margin-right: 10px; */
            cursor: pointer;
        }

        .star-radio input[type="radio"]:checked+.star {
            display: inline-block;
            width: 16px;
            height: 16px;
            background-image: url({{ asset('assets/images/star_fill.png') }});
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            /* margin-right: 10px; */
            cursor: pointer;
        }

        #datatable_wrapper:nth-child(3) {
            position: fixed;
        }

        /* .dataTables_filter {
                                                                                                                                                                                                                                                    position: relative;
                                                                                                                                                                                                                                                } */

        /* .dataTables_filter input {
                                                                                                                                                                                                                                                    width: 250px;
                                                                                                                                                                                                                                                    height: 32px;
                                                                                                                                                                                                                                                    background: #fcfcfc;
                                                                                                                                                                                                                                                    border: 1px solid #000000;
                                                                                                                                                                                                                                                    border-radius: 5px;
                                                                                                                                                                                                                                                    text-indent: 10px;
                                                                                                                                                                                                                                                } */

        /* .dataTables_filter .fa-search {
                                                                                                                                                                                                                                                    position: absolute;
                                                                                                                                                                                                                                                    top: 10px;
                                                                                                                                                                                                                                                    left: auto;
                                                                                                                                                                                                                                                    right: -0%;
                                                                                                                                                                                                                                                } */

        .hidden_text {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }


        @media (max-width: 1440px) and (min-width: 400px) {
            body {
                font-size: 10px !important;
            }

            .funnel {
                font-size: 10px !important;
            }

            .funnel1 {
                font-size: 10px !important;
            }
        }

        .timeline-marker-web {
            top: 0;
            bottom: 0;
            left: 0;
            width: 15px;
            display: table-cell;
            position: relative;
            vertical-align: top;
        }

        .timeline-marker-web:before {
            border: 3px solid transparent;
            border-radius: 100%;
            content: "";
            display: block;
            height: 15px;
            position: absolute;
            top: 0px;
            left: 0;
            width: 15px;
            transition: background 0.3s ease-in-out, border 0.3s ease-in-out;
            background-position: center;
            background-image: url({{ asset('assets/images/timeline/web.svg') }});
        }

        .timeline-marker-web:after {
            content: "";
            width: 1px;
            background: #000000;
            display: block;
            position: absolute;
            top: 15px;
            bottom: 0;
            left: 7px;
        }

        .timeline-marker-android {
            top: 0;
            bottom: 0;
            left: 0;
            width: 15px;
            display: table-cell;
            position: relative;
            vertical-align: top;
        }

        .timeline-marker-android:before {
            background: #d3d3d3;
            border: 3px solid transparent;
            border-radius: 100%;
            content: "";
            display: block;
            height: 15px;
            position: absolute;
            top: 0px;
            left: 0;
            width: 15px;
            transition: background 0.3s ease-in-out, border 0.3s ease-in-out;
            background-position: center;
            background-image: url({{ asset('assets/images/timeline/android.svg') }});
        }

        .timeline-marker-android:after {
            content: "";
            width: 1px;
            background: #000000;
            display: block;
            position: absolute;
            top: 15px;
            bottom: 0;
            left: 7px;
        }

        .timeline-marker-iphone {
            top: 0;
            bottom: 0;
            left: 0;
            width: 15px;
            display: table-cell;
            position: relative;
            vertical-align: top;
        }

        .timeline-marker-iphone:before {
            background: #d3d3d3;
            border: 3px solid transparent;
            border-radius: 100%;
            content: "";
            display: block;
            height: 15px;
            position: absolute;
            top: 0px;
            left: 0;
            width: 15px;
            transition: background 0.3s ease-in-out, border 0.3s ease-in-out;
            background-position: center;
            background-image: url({{ asset('assets/images/timeline/ios.svg') }});
        }

        .timeline-marker-iphone:after {
            content: "";
            width: 1px;
            background: #000000;
            display: block;
            position: absolute;
            top: 15px;
            bottom: 0;
            left: 7px;
        }
    </style>
    <link href="{{ asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet"
        type="text/css">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="d-lg-flex" id="custom_height" style="">
                    <div class="chat-leftsidebar me-lg-3">
                        <div class="tab-content py-1">
                            <input type="hidden" name="" value="0" id="hidden_status">
                            <input type="hidden" name="" value="0" id="hidden_is_advancefilter">
                            <div class="tab-pane show active lead-list" id="chat">
                                @csrf
                                <table id="datatable" class="table static_hover dt-responsive nowrap w-100" style="">
                                    <thead class="d-none">
                                        <tr>
                                            <th>data</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="w-100 user-chat py-1">
                        <div class="" id="lead_detail" style="">

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalPointLapsed" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
            role="dialog" aria-labelledby="modalInquiryLogLabel" aria-hidden="true" style="z-index: 1600;">
            <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLeadLogLabel">Point Lapsed</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="min-height:100%;">
                        <form enctype="multipart/form-data" id="formLeadPointLapsed"
                            action="{{ route('lead.answer.save') }}" method="POST" class="needs-validation" novalidate>
                            @csrf
                            <input type="hidden" name="file_id" id="lapsed_file_id">
                            <input type="hidden" name="file_tag_id" id="lapsed_file_tag_id">
                            <input type="hidden" name="file_status" id="lapsed_file_status">
                            <input type="hidden" name="file_amount" id="lapsed_file_amount" value="0">
                            <input type="hidden" name="file_point" id="lapsed_file_point" value="0">
                            <div id="PointLapsedBody">

                            </div>
                            <button class="btn btn-primary" type="submit">Save</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalPointQuery" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
            role="dialog" aria-labelledby="modalInquiryLogLabel" aria-hidden="true" style="z-index: 1600;">
            <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLeadLogLabel">Point Query</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="min-height:100%;">
                        <form enctype="multipart/form-data" id="formHodPointQuery"
                            action="{{ route('lead.answer.save') }}" method="POST" class="needs-validation" novalidate>
                            @csrf
                            <input type="hidden" name="file_id" id="query_file_id">
                            <input type="hidden" name="file_tag_id" id="query_file_tag_id">
                            <input type="hidden" name="file_status" id="query_file_status">
                            <input type="hidden" name="file_amount" id="query_file_amount" value="0">
                            <input type="hidden" name="file_point" id="query_file_point" value="0">
                            <div id="PointQueryBody">
                            </div>
                            <button class="btn btn-primary" type="submit">Save</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalStatusManu" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
            role="dialog" aria-labelledby="modalInquiryLogLabel" aria-hidden="true" style="z-index: 1600;">
            <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLeadLogLabel">Status Menu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>


                    <div class="modal-body" style="min-height:100%;">
                        <form enctype="multipart/form-data" action="{{ route('lead.answer.save') }}" method="POST"
                            id="formLeadPointQuery" class="needs-validation" novalidate>
                            @csrf
                            <select class="form-control select2-ajax" id="is_hod_approved">
                                <option value="0" selected>Please select a status</option>
                                <option value="1">Approved</option>
                                <option value="2">Rejected</option>
                                <option value="3">Query</option>
                            </select>

                            {{-- <div id="queryQution" class="hidden"> --}}
                            <input type="hidden" name="file_id" id="hod_query_file_id">
                            <div id="HodQueryBody">
                            </div>
                            {{-- </div> --}}
                            <a class="btn btn-primary mt-2" id="SaveHodQueryQuestion">Save</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="modalHodStatus" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
            role="dialog" aria-labelledby="modalInquiryLogLabel" aria-hidden="true" style="z-index: 1600;">
            <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLeadLogLabelHOD">Query Menu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>


                    <div class="modal-body" style="min-height:100%;">
                      
                        <input type="hidden" name="file_id" id="hod_status_query_file_id">
                        <div id="HodStatusBody">
                        </div>

                        <select class="form-control select2-ajax" id="is_hod_approved_status">
                            <option value="0" selected>Please select a status</option>
                            <option value="1">Approved</option>
                            <option value="2">Rejected</option>
                        </select>
                        <button class="btn btn-primary mt-2" type="submit"
                            onclick="HodApproved('modalHodStatus')">Save</button>

                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalLeadFile" data-bs-backdrop="static" tabindex="-1" role="dialog"
            aria-labelledby="modalLeadFileLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLeadFileLabel"> Lead File</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form enctype="multipart/form-data" id="formLeadFile" action="{{ route('crm.lead.file.save') }}"
                        method="POST" class="needs-validation" novalidate>
                        <div class="modal-body">
                            @csrf
                            <div class="col-md-12 text-center loadingcls">
                                <button type="button" class="btn btn-light waves-effect">
                                    <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                                </button>
                            </div>

                            <input type="hidden" name="lead_file_lead_id" id="lead_file_lead_id">

                            <div class="row">
                                <div class="col-md-12">

                                    <div class="row mb-1">
                                        <label for="horizontal-firstname-input"
                                            class="col-sm-3 col-form-label">Tag</label>
                                        <div class="col-sm-9">
                                            <select class="form-control select2-ajax" id="lead_file_tag_id"
                                                name="lead_file_tag_id" required>
                                                <option value="3">Bill</option>
                                            </select>
                                            <div class="invalid-feedback">Please select tag</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">

                                    <div class="row mb-1">
                                        <label for="horizontal-firstname-input"
                                            class="col-sm-3 col-form-label">File</label>
                                        <div class="col-sm-9">
                                            <input type="file" class="form-control" id="lead_file_file_name"
                                                name="lead_file_file_name[]" placeholder="File" value="" required
                                                multiple>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">


                            <div>


                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button id="btnSaveFile" type="submit" class="btn btn-primary save-btn">Save</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalClaimReport" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
            role="dialog" aria-labelledby="modalClaimLabel" aria-hidden="true" style="z-index: 1400;">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalClaimLabel">Claim Report</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="min-height:100%;">
                        <table class="table align-middle table-nowrap mb-0 w-100 dataTable no-footer">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Bill Name</th>
                                    <th>Bill Amount</th>
                                    <th>Bill Point</th>
                                    <th>Claim / Lapsed</th>
                                    <th>Hod Approved</th>
                                </tr>
                            </thead>
                            <tbody id="ClaimeReportBody">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- container-fluid -->
    </div>

    @include('crm.reward.create_lead_modal')
@endsection('content')
@section('custom-scripts')
    <script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
    <script type="text/javascript">
        var ajaxURLDataListAjax = "{{ route('reward.list.ajax') }}";
        var ajaxURLDataDatail = "{{ route('crm.lead.detail') }}";
        var ajaxURLDataDatail = "{{ route('reward.detail') }}";
        var ajaxURLDatail = "{{ route('reward.detail') }}";
        var ajaxURLPointQueryQuestion = "{{ route('crm.lead.point.query.question') }}";
        var ajaxURLPointQueryQuestionAnswer = "{{ route('crm.lead.point.query.question.answer') }}";
        var ajaxURLPointAjax = "{{ route('crm.lead.point.ajax') }}";
        var ajaxURLSaveBillingAmount = "{{ route('crm.lead.save.billing.amount') }}";
        var ajaxURLPointHodApprove = "{{ route('point.hod.approve') }}";
        var ajaxURLCompanyAdminAllTask = "{{ route('reward.company.admin.all.task') }}";
        var ajaxURLGetRewardBillStatus = "{{ route('crm.lead.get.reward.bill.status') }}";
        var ajaxURLUpdateALL = "{{ route('crm.lead.update.all') }}";
        var ajaxURLUpdateSave = "{{ route('crm.lead.update.save') }}";

        var csrfToken = $("[name=_token").val();
        let previousLeadDetailRequest = null;
        var lead_id = 0;
        let advanceFilterList = '';
        var table = $('#datatable').DataTable({
            "aoColumnDefs": [{
                "bSortable": true,
                "aTargets": [0]
            }],
            "pageLength": 10,
            "scrollX": false,
            "scrollY": 600,
            "order": [
                [0, 'desc']
            ],
            "processing": true,
            "serverSide": true,
            "bInfo": false,
            "ajax": {
                "url": ajaxURLDataListAjax,
                "type": "POST",
                "data": {
                    "_token": $("[name=_token").val(),
                    'isAdvanceFilter': function() {
                        return $('#hidden_is_advancefilter').val();
                    },
                    'AdvanceData': function() {
                        return advanceFilterList;
                    },
                }
            },
            "aoColumns": [{
                "mData": "view"
            }, ],
            "pagingType": "full_numbers",
            "language": {
                "search": "",
                "sLengthMenu": "_MENU_",
                "paginate": {
                    "previous": "<",
                    "next": ">",
                    "first": "|<",
                    "last": ">|"
                }
            },
        });

        $(document).ready(function() {
            var options = {
                beforeSubmit: showRequest, // pre-submit callback
                success: showResponse // post-submit callback
            };

            // bind form using 'ajaxForm'
            $("#formLeadPointQuery").ajaxForm(options);
            $("#formHodPointQuery").ajaxForm(options);
            $("#formLeadPointLapsed").ajaxForm(options);
            $("#formLeadFile").ajaxForm(options);
            $('#formLead').ajaxForm(options);
            // $("#formLeadStatusChange").ajaxForm(options);
        });

        $.fn.DataTable.ext.pager.numbers_length = 5;
        table.on('xhr', function() {

            
            var responseData = table.ajax.json();
            $('#list_record_count').html(responseData['recordsFiltered']);
            getDataDetail(responseData['FirstPageLeadId']);
            $('#list_data_loader').hide();
        });

        $('#datatable_length').each(function() {
            $(this).before(
                '<div><i id="list_data_loader" class="bx bx-loader bx-spin font-size-16 align-middle me-2" style=""></i><b>{{ $data['title'] }} (<span class="text-primary" style="font-weight: bold;" id="list_record_count"></span>)</b></div>'
            );
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

            $(".save-btn").html("Saving...");
            $(".save-btn").prop("disabled", true);
            return true;
        }

        function showResponse(responseText, statusText, xhr, $form) {

            $(".save-btn").html("Save");
            $(".save-btn").prop("disabled", false);

            if ($form[0]['id'] == "formLeadPointQuery") {
                if (responseText['status'] == 1) {
                    toastr["success"](responseText['msg']);
                    $('#modalPointQuery').modal('hide');
                    RewardTableReload();
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
            } else if ($form[0]['id'] == "formLeadPointLapsed") {
                if (responseText['status'] == 1) {
                    toastr["success"](responseText['msg']);
                    $('#modalPointLapsed').modal('hide');
                    RewardTableReload();
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
            } else if ($form[0]['id'] == "formLeadFile") {
                if (responseText['status'] == 1) {
                    toastr["success"](responseText['msg']);
                    $('#modalLeadFile').modal('hide');
                    $('#formLeadFile').trigger("reset");
                    RewardTableReload();
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
            } else if ($form[0]['id'] == "formHodPointQuery") {
                if (responseText['status'] == 1) {
                    toastr["success"](responseText['msg']);
                    $('#modalStatusManu').modal('hide');
                    RewardTableReload();
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
            } else if ($form[0]['id'] == "formLead") {
                if (responseText['status'] == 1) {
                    toastr["success"](responseText['msg']);
                    $('#formLead').trigger("reset");
                    $("#modalLead").modal('hide');
                    table.ajax.reload();

                } else if (responseText['status'] == 0) {
                    if (typeof responseText['data'] !== "undefined") {
                        var size = Object.keys(responseText['data']).length;
                        if (size > 0) {
                            for (var [key, value] of Object.entries(responseText['data'])) {
                                $('#phone_no_error_dialog').show();
                                $('#error_text').text(responseText['msg']);
                            }
                        }else{
                            $('#phone_no_error_dialog').show();
                            $('#error_text').text(responseText['msg']);
                        }
                    } else {
                        $('#phone_no_error_dialog').show();
                        $('#error_text').text(responseText['msg']);
                    }
                }
            }
        }



        $(document).ready(function() {
            $('#datatable_length').parent().removeClass().addClass(
                'col-11 d-flex justify-content-between align-items-center card-header py-2 px-2');
            $('#datatable_length').parent().parent().addClass('justify-content-center');
            $('#datatable_length label').addClass('m-0');
            $('#datatable_filter').parent().removeClass().addClass('col-12');
            $('#datatable_filter label').addClass('input-group position-relative mb-0');
            $('#datatable_paginate').parent().removeClass().addClass('col-12 d-flex justify-content-center');
        });

        function getDataDetail(id) {
            if (id != 0 && id != null && id != undefined) {
                $("#lead_" + id).parent().parent().addClass('active_lead');
                if (previousLeadDetailRequest) {
                    previousLeadDetailRequest.abort();
                }
                $('#list_data_loader').show();
                previousLeadDetailRequest = $.ajax({
                    type: 'GET',
                    url: ajaxURLDataDatail + "?id=" + id,
                    success: function(resultData) {
                        if (resultData['status'] == 1) {

                            $(".lead_li").parent().parent().removeClass('active_lead');
                            $("#lead_" + id).parent().parent().addClass('active_lead');
                            $("#lead_detail").html(resultData['view']);
                            var data = resultData['data']['lead'];
                            lead_id = data['id'];

                            $('#point_claimed_count').text(resultData['data']['LeadBillSummary_claimed']);
                            $('#point_query_count').text(resultData['data']['LeadBillSummary_query']);
                            $('#point_lapsed_count').text(resultData['data']['LeadBillSummary_laps']);

                            $('#list_data_loader').hide();
                            RewardPoint();
                        }
                    }
                })
            }
        }

        function adjustContainerHeight() {
            var windowWidth = $(window).width();
            if (windowWidth <= 1440) {
                $('body').addClass('vertical-collpsed');
            }
            if (is_deal == 1) {
                var windowHeight = $(window).height() - 150;
                max_height = windowHeight - 180;
                $('#datatable').parent().css('height', max_height + 'px');
                $('#datatable').parent().css('max-height', max_height + 'px');
                $('#home').parent().css('max-height', max_height + 'px');
            } else {
                var windowHeight = $(window).height() - 100;
                max_height = windowHeight - 180;
                $('#datatable').parent().css('max-height', max_height + 'px');
                $('#datatable').parent().css('height', max_height + 'px');
                $('#home').parent().css('max-height', max_height + 'px');
            }
            $('#custom_height').css('height', windowHeight + 'px');
        }

        function PointQuery(file_id, status) {
            $.ajax({
                type: 'GET',
                data: {
                    "lead_id": lead_id,
                },
                url: ajaxURLPointQueryQuestion,
                success: function(responseText) {
                    if (responseText['status'] == 1) {
                        $('#modalPointQuery').modal('show');
                        $('#query_file_id').attr('value', $('#hidden_file_id_' + file_id).val());
                        $('#query_file_tag_id').attr('value', $('#hidden_file_tag_' + file_id).val());
                        $('#query_file_status').attr('value', status);
                        $('#query_file_amount').val($('#' + file_id).val());
                        $('#query_file_point').val($('#point_' + file_id).val());
                        $('#PointQueryBody').html(responseText['view']);
                    } else {
                        toastr["error"](responseText['msg']);
                    }
                }
            });

        }

        function PointLapsed(file_id, status) {
            $.ajax({
                type: 'GET',
                data: {
                    "lead_id": lead_id,
                },
                url: ajaxURLPointQueryQuestion,
                success: function(responseText) {
                    if (responseText['status'] == 1) {
                        $('#modalPointLapsed').modal('show');
                        $('#lapsed_file_id').attr('value', $('#hidden_file_id_' + file_id).val());
                        $('#lapsed_file_tag_id').attr('value', $('#hidden_file_tag_' + file_id).val());
                        $('#lapsed_file_status').attr('value', status);
                        $('#lapsed_file_amount').val($('#' + file_id).val());
                        $('#lapsed_file_point').val($('#point_' + file_id).val());
                        $('#PointLapsedBody').html(responseText['view']);
                    } else {
                        toastr["error"](responseText['msg']);
                    }
                }
            });
        }

        function RewardPoint(is_reload = 0) {
            if (is_reload == 0) {
                $('#modalRewardPoint').modal('show');
            }

            var RewardPointTable = $('#RewardPoint').DataTable({
                "aoColumnDefs": [{
                    "bSortable": false,
                    "aTargets": [0, 1, 2, 3]
                }, ],
                "sDom": "lrtip",
                "bInfo": false,
                "order": [
                    [0, 'desc']
                ],
                "processing": true,
                "serverSide": true,
                "bDestroy": true,
                "pageLength": 10,
                "ajax": {
                    "url": ajaxURLPointAjax,
                    "type": "POST",
                    "data": {
                        "_token": csrfToken,
                        "lead_id": function() {
                            return lead_id;
                        },
                    }
                },
                "aoColumns": [{
                        "mData": "bill_attached",

                    },
                    {
                        "mData": "bill_amount",

                    },
                    {
                        "mData": "point",

                    },
                    {
                        "mData": "query",
                    },
                    {
                        "mData": "lapsed",
                    },
                    {
                        "mData": "action",
                    },
                    {
                        "mData": "hod_approved",
                    }
                ],
            });



            $('#RewardPoint_paginate').addClass('float-start');
        }

        function RewardTableReload() {
            RewardPoint(1);
            CompanyAdminAllTask();
        }

        function SaveBillingAmount(file_id) {

            $('#claimed_btn_' + file_id).html('Claimed...').attr('disabled');


            billing_amount = $('#' + file_id).val(),
                point = $('#point_' + file_id).val(),

                $.ajax({
                    type: 'POST',
                    url: ajaxURLSaveBillingAmount,
                    data: {
                        'lead_id': lead_id,
                        'file_id': file_id,
                        'billing_amount': billing_amount,
                        'point': point,
                        '_token': $("[name=_token]").val()
                    },
                    success: function(responseText) {
                        if (responseText['status'] == 1) {
                            $('#claimed_btn_' + file_id).html('Claim').removeAttr('disabled');
                            toastr["success"](responseText['msg']);
                            RewardTableReload();
                        } else {
                            toastr["error"](responseText['msg']);
                            $('#claimed_btn_' + file_id).html('Claim').removeAttr('disabled');
                        }


                    }
                });
        }

        function changepoint(id) {
            var billing_amt = $('#' + id).val();
            var point = billing_amt / 1000;

            $('#' + id).attr('data-point', point);
            $('#point_' + id).attr('value', point);
        }

        function addLeadFileModal(id) {
            $("#modalLeadFile").modal('show');
            $("#formLeadFile .loadingcls").hide();
            $("#lead_file_lead_id").val(id);
        }

        $("#lead_file_tag_id").select2();
        $("#is_hod_approved_status").select2();
        $('#is_hod_approved').select2().on('change', function() {

            var selectedValue = $(this).val();

            if (selectedValue == '3') {
                queryQuestion();
                $('#HodQueryBody').show();
            } else {
                $('#HodQueryBody').hide();
            }

        });

        function HodApproved(modalType) {

            if (modalType === 'modalStatusManu') {
                file_id = $('#hod_query_file_id').val();
                point = $('#hod_query_file_point').val();
                isHodApprovedValue = $('#is_hod_approved').val();
            } else if (modalType === 'modalHodStatus') {
                file_id = $('#hod_status_query_file_id').val();
                point = $('#hod_query_file_point_id').val();
                isHodApprovedValue = $('#is_hod_approved_status').val();
            } else {
                return;
            }

            $.ajax({
                type: 'POST',
                url: ajaxURLPointHodApprove,
                data: {
                    'file_id': file_id,
                    'is_hod_approve': isHodApprovedValue,
                    '_token': $("[name=_token]").val()
                },
                success: function(responseText) {
                    if (responseText['status'] == 1) {
                        toastr["success"](responseText['msg']);
                        RewardTableReload();
                        $('#' + modalType).modal('hide');
                    } else {
                        toastr["error"](responseText['msg']);
                    }
                }
            });
        }

        function resetModalStatusManu() {
            $('#formLeadPointQuery')[0].reset();
            $('#formLeadPointQuery').find('.is-invalid').removeClass('is-invalid');
            // For example:
            $('#queryQution').addClass('hidden');
            $('#HodQueryBody').empty();
            $('#is_hod_approved').trigger('change');
        }
        $('#modalStatusManu').on('hidden.bs.modal', function(e) {
            resetModalStatusManu();
        });

        function resetModalHOD() {
            $('#is_hod_approved_status').val(0);
        }
        $('#modalHodStatus').on('hidden.bs.modal', function(e) {
            resetModalHOD();
        });


        function CompanyAdminAllTask() {
            $.ajax({
                type: 'GET',
                url: ajaxURLCompanyAdminAllTask,
                data: {
                    'lead_id': lead_id,
                    '_token': $("[name=_token]").val()
                },
                success: function(responseText) {
                    if (responseText['status'] == 1) {
                        $('#reward_company_admin_table_body').html(responseText['data']);
                    }
                }
            });
        }

        $('#saveAdvanceFilter').on('click', function() {

            $("#saveAdvanceFilter").html(
                '<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span  >Saving...</span>');
            reloadLeadList(0, 1);
            ischeckFilter();
        });

        function reloadLeadList(status = 0, isAdvanceFilter = 0) {

            let tempadvanceFilterList = [];

            $('#hidden_is_advancefilter').attr('value', isAdvanceFilter);

            tempadvanceFilterList.push({
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
                tempadvanceFilterList.push({
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
            $('#list_record_count').html('<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i>');

            advanceFilterList = JSON.stringify(tempadvanceFilterList)
            table.ajax.reload();
        }

        window.smoothScroll = function(target) {
            var scrollContainer = target;


            do { //find scroll container
                scrollContainer = scrollContainer.parentNode.parentNode;
                if (!scrollContainer) return;
                scrollContainer.scrollTop += 1;
            } while (scrollContainer.scrollTop == 0);

            var targetY = 0;
            do { //find the top of target relatively to the container
                if (target == scrollContainer) break;
                targetY += target.offsetTop;
            } while (target = target.offsetParent);

            scroll = function(c, a, b, i) {
                i++;
                if (i > 30) return;
                c.scrollTop = a + (b - a) / 30 * i;
                setTimeout(function() {
                    scroll(c, a, b, i);
                }, 20);
            }

            targetY = targetY - 300
            scroll(scrollContainer, scrollContainer.scrollTop, targetY, 0);
        }

        function getRewardBillStatus(lead_id) {
            $("#modalClaimReport").modal('show');
            $.ajax({
                type: 'GET',
                url: ajaxURLGetRewardBillStatus,
                data: {
                    "lead_id": lead_id,
                },
                success: function(responseText) {

                    if (responseText['status'] == 1) {
                        $('#ClaimeReportBody').html(responseText['data']);
                    }
                }
            })
        }

        function viewAllLeadUpdates(id) {
            $('#note_loader').show();

            var scrollTopHeightDataTable = $('#lead_detail .lead-custom-scroll-2').prop('scrollTop');
            $.ajax({
                type: 'GET',
                url: ajaxURLUpdateALL + "?lead_id=" + id + "islimit=0",
                success: function(resultData) {
                    if (resultData['status'] == 1) {
                        $("#tab_notes").html(resultData['view']);
                        $('#note_loader').hide();
                        $("#lead_detail .lead-custom-scroll-2").animate({
                            scrollTop: scrollTopHeightDataTable
                        }, 10);
                    }
                }
            });

        }

        function saveUpdate(id) {

            var leadUpdate = $("#lead_update").val()

            $.ajax({
                type: 'POST',
                url: ajaxURLUpdateSave,
                data: {
                    'lead_id': id,
                    'lead_update': leadUpdate,
                    '_token': $("[name=_token]").val()
                },
                success: function(responseText) {
                    if (responseText['status'] == 1) {
                        $('#note_loader').show();
                        $("#lead_update").val('');
                        toastr["success"](responseText['msg']);
                        $.ajax({
                            type: 'GET',
                            url: ajaxURLUpdateALL + "?lead_id=" + id + "&islimit=1",
                            success: function(resultData) {
                                if (resultData['status'] == 1) {
                                    $("#tab_notes").html(resultData['view']);
                                    $('#note_loader').hide();
                                }
                            }
                        });
                    } else {

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
                        $('#note_loader').hide();

                    }
                }
            });

        }

        function StatusApproved(id, lead_id, point) {

            $('#modalStatusManu').modal('show');

            $('#hod_query_file_id').val(id);
            $('#hod_query_file_point').val(point);
        }

        function queryQuestion() {


            $.ajax({
                type: 'GET',
                data: {
                    "lead_id": lead_id,
                },
                url: ajaxURLPointQueryQuestion,
                success: function(responseText) {

                    if (responseText['status'] == 1) {
                        $('#HodQueryBody').html(responseText['view']);
                    } else {
                        toastr["error"](responseText['msg']);
                    }
                }
            });
        }

        function HodQueryShow(id, lead_id, point) {
                
                
                $('#hod_status_query_file_id').val(id);
                $('#hod_query_file_point_id').val(point);
                $('#modalHodStatus').modal('show');
                $('#is_hod_approved_status').trigger('change');
                $.ajax({
                type: 'GET',
                data: {
                    "lead_id": lead_id,
                    "file_id": id,
                },
                url: ajaxURLPointQueryQuestionAnswer,
                success: function(responseText) {
                    if (responseText['status'] == 1) {
                        $('#HodStatusBody').html(responseText['view']);
                    } else {
                        toastr["error"](responseText['msg']);
                    }
                }
            });

        }

        $('#SaveHodQueryQuestion').on('click', function() {
            HodApproved('modalStatusManu')

            if ($('#is_hod_approved').val() == 3) {
                $('#formLeadPointQuery').submit();
            }
        })
    </script>
    @include('crm.reward.create_lead_script');
@endsection
