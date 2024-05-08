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


        /* TOOLTIP START */
        .hover_tooltip {
            position: relative;
            display: inline-block;
            /* border-bottom: 1px dotted black; */
        }

        .hover_tooltip .tooltiptext {
            visibility: hidden;
            width: max-content;
            background-color: #556ee6;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px 10px;
            position: absolute;
            z-index: 1;
            top: -5px;
            left: 80%;
        }

        .hover_tooltip .tooltiptext::after {
            content: "";
            position: absolute;
            top: 50%;
            right: 100%;
            margin-top: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: transparent #556ee6 transparent transparent;
        }

        .hover_tooltip:hover .tooltiptext {
            visibility: visible;
        }

        .modal-reduis {
            border-radius: 15px;
            /* border-top-right-radius: 15px;
                        border-bottom-right-radius: 15px; */
        }


        .bg-card-color {
            background-image: linear-gradient(to right, #6779af, #6779af);
            /* border-top-right-radius: 5px; */
            border-bottom-left-radius: 15px;
            border-top-left-radius: 15px;

        }

        #channelPratnerDetail p {
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
        }

        #architectDetail p {
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
        }

        #electricianDetail p {
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
        }

        /* TOOLTIP END */
    </style>

    <link href="{{ asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet"
        type="text/css">

    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                @if ($data['is_deal'] == 1)
                    <div class="row ms-1">
                        <div class="bg-light p-2" style="width: fit-content;">
                            <div class="d-flex flex-column ms-3 me-3">
                                <b>{{ $data['title'] }}</b>
                                <span class="text-primary" style="font-weight: bold;" id="list_record_count"></span>
                            </div>
                        </div>
                        <div class="bg-light p-2" style="width: fit-content;">
                            <div class="d-flex flex-column me-3">
                                <b>Whitelion Amt</b>
                                <span class="text-primary" style="font-weight: bold;" id="total_whitelion_amt">0/-</span>
                            </div>
                        </div>
                        <div class="bg-light p-2" style="width: fit-content;">
                            <div class="d-flex flex-column me-3">
                                <b>Billing Amt</b>
                                <span class="text-primary" style="font-weight: bold;" id="total_billing_amt">0/-</span>
                            </div>
                        </div>
                        <div class="bg-light p-2" style="width: fit-content;">
                            <div class="d-flex flex-column me-3">
                                <b>Total Amt</b>
                                <span class="text-primary" style="font-weight: bold;" id="total_quotation_amt">0/-</span>
                            </div>
                        </div>
                    </div>
                @elseif (isArchitect() == 1 || isElectrician() == 1)
                    <div class="row ms-1">
                        <div class="bg-light p-2" style="width: fit-content;">
                            <div class="d-flex flex-column ms-3 me-3">
                                <b>{{ $data['title'] }}</b>
                                <span class="text-primary" style="font-weight: bold;" id="list_record_count"></span>
                            </div>
                        </div>
                        <div class="bg-light p-2" style="width: fit-content;">
                            <div class="d-flex flex-column me-3">
                                <b>Whitelion Amt</b>
                                <span class="text-primary" style="font-weight: bold;" id="total_whitelion_amt">0/-</span>
                            </div>
                        </div>
                        <div class="bg-light p-2" style="width: fit-content;">
                            <div class="d-flex flex-column me-3">
                                <b>Billing Amt</b>
                                <span class="text-primary" style="font-weight: bold;" id="total_billing_amt">0/-</span>
                            </div>
                        </div>
                        <div class="bg-light p-2" style="width: fit-content;">
                            <div class="d-flex flex-column me-3">
                                <b>Total Amt</b>
                                <span class="text-primary" style="font-weight: bold;" id="total_quotation_amt">0/-</span>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="row ms-1">
                    {{-- <div class="d-lg-flex" style="height: 700px;"> --}}
                    <div class="d-lg-flex" id="custom_height" style="">
                        <div class="chat-leftsidebar me-lg-3">
                            <div class="tab-content py-1">
                                <input type="hidden" name="" value="0" id="hidden_status">
                                <input type="hidden" name="" value="0" id="hidden_is_advancefilter">
                                <div class="tab-pane show active lead-list" id="chat">
                                    <table id="datatable" class="table static_hover dt-responsive nowrap w-100"
                                        style="">
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
                            {{-- <div class="" id="lead_detail" style="height: 700px;"> --}}
                            <div class="" id="lead_detail" style="">

                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- container-fluid -->
        </div>

        <div class="modal fade" id="modalQuotation" data-bs-backdrop="static" tabindex="-1" role="dialog"
            aria-labelledby="modalQuotationLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-s" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalQuotationLabel">Quotation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form enctype="multipart/form-data" id="formLeadQuotation"
                        action="{{ route('crm.lead.quotation.save') }}" method="POST" class="needs-validation" novalidate>
                        <div class="modal-body">
                            @csrf
                            <div class="col-md-12 text-center loadingcls">
                                <button type="button" class="btn btn-light waves-effect">
                                    <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                                </button>
                            </div>
                            <input type="hidden" name="lead_quotation_lead_id" id="lead_quotation_lead_id">
                            <div class="row mb-1">
                                <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Quotation</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" id="lead_quotation" name="lead_quotation"
                                        placeholder="Quotation" value="" required>
                                </div>
                            </div>
                            <div class="row mb-1">
                                <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Quotation
                                    File</label>
                                <div class="col-sm-9">
                                    <input type="file" class="form-control" id="lead_quotation_file"
                                        name="lead_quotation_file[]" placeholder="Quotation File" value="" required
                                        multiple>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <div>
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary save-btn">Save</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        @include('crm.lead.action_modal')



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

                                            </select>
                                            <div class="invalid-feedback">
                                                Please select tag
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="row mb-2">
                                        <input type="hidden" name="count_of_bill" id="count_of_bill" value="1">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label mb-2" id="fileInputLabel">File</label>
                                        <div class="col-md-4" id="bill_number_field" style="display: none;">
                                            <input type="text" class="form-control" id="bill_number_0" name="bill_number_0" placeholder="Bill Number" required>
                                            <div class="invalid-feedback">
                                                Please select Bill Number
                                            </div>
                                        </div>
                                        <div class="col-sm-9">
                                            <input type="file" class="form-control" id="lead_file_file_name_0" name="lead_file_file_name_0[]" placeholder="File" required multiple>
                                        </div>
                                        <div id="advanceBillRows" style="display: none;"></div>
                                        <div class="bg-white">
                                            <a id="btnAddBill" class="" style="cursor: pointer; display: none;">+ Add New Bill</a>
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

        <div class="modal fade" id="modalLeadContact" data-bs-backdrop="static" tabindex="-1" role="dialog"
            aria-labelledby="modalLeadContactLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLeadContactLabel"> Lead Contact</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form id="formLeadContact" action="{{ route('crm.lead.contact.save') }}" method="POST"
                        class="needs-validation" novalidate>
                        <div class="modal-body">
                            @csrf
                            <div class="col-md-12 text-center loadingcls">
                                <button type="button" class="btn btn-light waves-effect">
                                    <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                                </button>
                            </div>

                            <input type="hidden" name="lead_contact_lead_id" id="lead_contact_lead_id">
                            <input type="hidden" name="lead_contact_id" id="lead_contact_id">
                            <div class="row">
                                <div class="col-md-6">

                                    <div class="row mb-1">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">First
                                            name</label>
                                        <div class="col-sm-9">
                                            <input class="form-control" id="lead_contact_first_name"
                                                name="lead_contact_first_name" placeholder="First Name" value=""
                                                required>
                                        </div>
                                    </div>

                                    <div class="row mb-1">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Phone
                                            number</label>
                                        <div class="col-sm-9">
                                            <div class="input-group">
                                                <div class="input-group-text">
                                                    +91
                                                </div>
                                                <input type="number" class="form-control" id="lead_contact_phone_number"
                                                    name="lead_contact_phone_number" placeholder="Phone number"
                                                    value="" required>

                                            </div>
                                        </div>


                                    </div>

                                    <div class="row mb-1">
                                        <label for="horizontal-firstname-input"
                                            class="col-sm-3 col-form-label">Email</label>
                                        <div class="col-sm-9">
                                            <input class="form-control" id="lead_contact_email" name="lead_contact_email"
                                                placeholder="Email" value="">
                                        </div>
                                    </div>


                                </div>

                                <div class="col-md-6">

                                    <div class="row mb-1">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Last
                                            name</label>
                                        <div class="col-sm-9">
                                            <input class="form-control" id="lead_contact_last_name"
                                                name="lead_contact_last_name" placeholder="Last Name" value=""
                                                required>
                                        </div>
                                    </div>

                                    <div class="row mb-1">
                                        <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Alternate
                                            Phone number</label>
                                        <div class="col-sm-9">
                                            <div class="input-group">
                                                <div class="input-group-text">
                                                    +91
                                                </div>
                                                <input type="number" class="form-control"
                                                    id="lead_contact_alernate_phone_number"
                                                    name="lead_contact_alernate_phone_number" placeholder="Phone number"
                                                    value="">

                                            </div>
                                        </div>


                                    </div>


                                    <div class="row mb-1">
                                        <label for="horizontal-firstname-input"
                                            class="col-sm-3 col-form-label">Tag</label>
                                        <div class="col-sm-9">
                                            <select class="form-control select2-ajax" id="lead_contact_tag_id"
                                                name="lead_contact_tag_id" required>

                                            </select>
                                            <div class="invalid-feedback">
                                                Please select tag
                                            </div>
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">


                            <div>


                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button id="btnSaveContact" type="submit" class="btn btn-primary save-btn">Save</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalRewardPoint" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
            role="dialog" aria-labelledby="modalInquiryLogLabel" aria-hidden="true" style="z-index: 1400;">
            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLeadLogLabel">Reward Point</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="min-height:100%;">

                    </div>
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

        <div class="modal fade" id="modalStatusMove" data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1"
            role="dialog" aria-labelledby="modalInquiryLogLabel" aria-hidden="true" style="z-index: 1600;">
            <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLeadLogLabel">Status Move</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="min-height:100%;">
                        <form enctype="multipart/form-data" id="formLeadStatusMove"
                            action="{{ route('save.lead.status.answer') }}" method="POST" class="needs-validation"
                            novalidate>
                            @csrf
                            <div id="StatusMoveBody">

                            </div>
                            <button class="btn btn-primary" type="submit">Save</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal fade" id="modalChannelPatDetail" data-bs-backdrop="static" tabindex="-1" role="dialog"
            aria-labelledby="modalchannelPatLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content" style="background: transparent; border: none">
                    <div class="modal-header" style="border: none">
                        <button type="button" class="btn-close bg-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="d-flex bg-white modal-reduis main-card">
                        <div class="col-3 bg-card-color">
                            <div class="text-center mt-4">
                                <img src="{{ asset('assets/images/logo-light (2).png') }}" style="width: 40%"><br>
                                <strong><span id="channel_partner_firm_name" class="text-white"></span></strong><br>
                                <span id="channel_partner_reference_type" class="badge bg-danger"></span>
                            </div>


                        </div>
                        <div class="col-9">
                            <div class="m-3">
                                <strong>
                                    <h5 class="text-black" style="border-bottom: 1px solid rgb(167, 163, 163) ">INFORMATION</h5>
                                </strong>
                                <div class="row mt-3" id="channelPratnerDetail">
                                    <div class="col-6">
                                        <label class="mb-0"><strong>Name</strong></label>
                                        <p id="channel_partner_first_name" title=""></p>
                                    </div>
                                    <div class="col-6">
                                        <label class="mb-0"><strong>Number</strong></label>
                                        <p id="channel_partner_number" title=""></p>
                                    </div>
                                    <div class="col-6">
                                        <label class="mb-0"><strong>Email</strong></label>
                                        <p id="channel_partner_email" title=""></p>
                                    </div>
                                    <div class="col-6">
                                        <label class="mb-0"><strong>Sales Parson</strong></label>
                                        <p id="sales_parson_name" title=""></p>
                                    </div>
                                </div>
                                <strong>
                                    <h5 class="text-black mt-2" style="border-bottom: 1px solid rgb(137, 133, 133)">ADDRESS</h5>
                                </strong>
                                <span id="channel_partner_address1"></span><br>
                                <span id="channel_partner_address2"></span><br>

                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>

        <div class="modal fade" id="modalArchitectDetail" data-bs-backdrop="static" tabindex="-1" role="dialog"
            aria-labelledby="modalarchitectLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content" style="background: transparent; border: none">
                    <div class="modal-header" style="border: none">
                        <button type="button" class="btn-close bg-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="d-flex bg-white modal-reduis main-card">
                        <div class="col-3 bg-card-color">
                            <div class="text-center mt-4">
                                <img src="{{ asset('assets/images/logo-light (2).png') }}" style="width: 40%"><br>
                                <strong><span id="architect_firm_name" class="text-white"></span></strong><br>
                                <span id="architect_reference_type" class="badge bg-danger"></span>
                            </div>


                        </div>
                        <div class="col-9">
                            <div class="m-3">
                                <strong>
                                    <h5 class="text-black" style="border-bottom: 1px solid rgb(167, 163, 163) ">INFORMATION</h5>
                                </strong>
                                <div class="row mt-3" id="architectDetail">
                                    <div class="col-6">
                                        <label class="mb-0"><strong>Name</strong></label>
                                        <p id="architect_first_name" title=""></p>
                                    </div>
                                    <div class="col-6">
                                        <label class="mb-0"><strong>Number</strong></label>
                                        <p id="architect_number" title=""></p>
                                    </div>
                                    <div class="col-6">
                                        <label class="mb-0"><strong>Email</strong></label>
                                        <p id="architect_email" title=""></p>
                                    </div>
                                    <div class="col-6">
                                        <label class="mb-0"><strong>Sales Parson</strong></label>
                                        <p id="sales_parson_name_arc" title=""></p>
                                    </div>
                                </div>
                                <strong>
                                    <h5 class="text-black mt-2" style="border-bottom: 1px solid rgb(137, 133, 133)">ADDRESS</h5>
                                </strong>
                                <span id="architect_address1"></span><br>
                                <span id="architect_address2"></span><br>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="modal fade" id="modalElectricianDetail" data-bs-backdrop="static" tabindex="-1" role="dialog"
            aria-labelledby="modalelectricianLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content" style="background: transparent; border: none">
                    <div class="modal-header" style="border: none">
                        <button type="button" class="btn-close bg-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="d-flex bg-white modal-reduis main-card">
                        <div class="col-3 bg-card-color">
                            <div class="text-center mt-4">
                                <img src="{{ asset('assets/images/logo-light (2).png') }}" style="width: 40%"><br>
                                <span id="electrician_reference_type" class="badge bg-danger"></span>
                            </div>


                        </div>
                        <div class="col-9">
                            <div class="m-3">
                                <strong>
                                    <h5 class="text-black" style="border-bottom: 1px solid rgb(167, 163, 163) ">INFORMATION</h5>
                                </strong>
                                <div class="row mt-3" id="electricianDetail">
                                    <div class="col-6">
                                        <label class="mb-0"><strong>Name</strong></label>
                                        <p id="electrician_first_name" title=""></p>
                                    </div>
                                    <div class="col-6">
                                        <label class="mb-0"><strong>Number</strong></label>
                                        <p id="electrician_number" title=""></p>
                                    </div>
                                    <div class="col-6">
                                        <label class="mb-0"><strong>Email</strong></label>
                                        <p id="electrician_email" title=""></p>
                                    </div>
                                    <div class="col-6">
                                        <label class="mb-0"><strong>Sales Parson</strong></label>
                                        <p id="sales_parson_name_ele" title=""></p>
                                    </div>
                                </div>
                                <strong>
                                    <h5 class="text-black mt-2" style="border-bottom: 1px solid rgb(137, 133, 133)">ADDRESS</h5>
                                </strong>
                                <span id="electrician_address1"></span><br>
                                <span id="electrician_address2"></span><br>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        @include('crm.lead.create_lead_modal')

    @endsection('content')

    @section('custom-scripts')
        <script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
        <script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
        <script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
        <script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/js/bootstrap-datepicker.js"></script>


        <script type="text/javascript">
            let previousLeadListRequest = null;
            let previousLeadDetailRequest = null;
            let previousLeadListAmountSummaryRequest = null;
            let isLeadAmountSummaryRefresh = 0;

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
            var ajaxURLSearchContactTag = "{{ route('crm.lead.search.contact.tag') }}";
            var ajaxURLSearchFileTag = "{{ route('crm.lead.search.file.tag') }}";
            var ajaxURLDeleteFile = "{{ route('crm.lead.file.delete') }}";
            var ajaxURLDataListAjax = "{{ route('crm.lead.list.ajax') }}";
            var ajaxURLDataListAmountSummaryAjax = "{{ route('crm.lead.list.amount.summary') }}";
            var ajaxURLDataDatail = "{{ route('crm.lead.detail') }}";
            var ajaxURLContactALL = "{{ route('crm.lead.contact.all') }}";
            var ajaxURLFileALL = "{{ route('crm.lead.file.all') }}";
            var ajaxURLUpdateALL = "{{ route('crm.lead.update.all') }}";
            var ajaxURLOpenActionAll = "{{ route('crm.lead.open.action.all') }}";
            var ajaxURLCloseActionALL = "{{ route('crm.lead.close.action.all') }}";
            var ajaxURLUpdateSave = "{{ route('crm.lead.update.save') }}";
            var ajaxURLStatusChange = "{{ route('crm.lead.status.change') }}";
            var ajaxURContactDetail = "{{ route('crm.lead.contact.detail') }}";
            var ajaxURQuotisFinalSave = "{{ route('crm.lead.change.final.quotation') }}";
            var ajaxURLViewLeadLog = "{{ route('crm.lead.view.log') }}";
            var ajaxURLRefreshStatusFunnel = "{{ route('crm.lead.refresh.status') }}";
            var ajaxURLFileStatusChange = "{{ route('crm.lead.file.status.change') }}";
            var ajaxURLGetRewardBillStatus = "{{ route('crm.lead.get.reward.bill.status') }}";
            var ajaxURChannelPartnerDetail = '{{ route('lead.channel.partner.detail') }}';
            var ajaxURLArchitectDetail = '{{ route('lead.architect.detail') }}';
            var ajaxURLElectricianDetail = '{{ route('lead.electrician.detail') }}';
            var ajaxSaveDetailURL = '{{ route('crm.lead.file.save') }}';

            var csrfToken = $("[name=_token").val();
            var is_deal = "{{ $data['is_deal'] }}";
            var viewLeadId = "{{ $data['id'] }}";

            var Lead_point_id = 0;

            var status = 0;
            var arc_prime = 0;
            var tag_id = 0;
            
            let advanceBillList = [];
            
            $(document).ready(function() {
                $('#lead_file_tag_id').change(function() {
                    var selectedTag = $(this).val();
                    if (selectedTag == 3) {
                        $('#bill_number_field').show();
                        $('#btnAddBill').show();
                        $('#advanceBillRows').show();
                        $('#fileInputLabel').text('Bill Number');
                        $('#lead_file_file_name_0').removeAttr('multiple');
                        $('#lead_file_file_name_0').closest('.col-sm-9').removeClass('col-sm-9').addClass('col-sm-5'); // Adjust column width
                    } else {
                        $('#bill_number_field').hide();
                        $('#advanceBillRows').hide();
                        $('#btnAddBill').hide();
                        $('#fileInputLabel').text('File');
                        $('#lead_file_file_name_0').attr('multiple', 'multiple');
                        $('#lead_file_file_name_0').closest('.col-sm-5').removeClass('col-sm-5').addClass('col-sm-9'); // Adjust column width
                    }
                });

                var filter_count = 1;
                $("#btnAddBill").click(function(event) {
                    event.preventDefault();
                    addNLoadfilter();
                });

                function addNLoadfilter(isedit = 0) {
                    var addadvanceBillRows = '<div class="row mb-1">';
                    addadvanceBillRows += '<input type="hidden" filt_id="' + filter_count + '" name="multi_bill_loop">';
                    addadvanceBillRows += '<div class="remove col-sm-2 d-flex justify-content-end" style="cursor: pointer; margin-left: 40px;">';
                    addadvanceBillRows += '<i class="bx bx-x-circle" style="font-size: large;"></i>';
                    addadvanceBillRows += '</div>';
                    addadvanceBillRows += '<div class="col-md-4">';
                    addadvanceBillRows += '<input type="text" class="form-control" id="bill_number_' + filter_count + '"  name="bill_number_' + filter_count + '" placeholder="Bill Number" required>';
                    addadvanceBillRows += '<div class="invalid-feedback">Please enter Bill Number</div>';
                    addadvanceBillRows += '</div>';
                    addadvanceBillRows += '<div class="col-md-5">';
                    addadvanceBillRows += '<input type="file" class="form-control" id="lead_file_file_name_' + filter_count + '" name="lead_file_file_name_' + filter_count + '[]" placeholder="File" required>';
                    addadvanceBillRows += '</div>';
                    addadvanceBillRows += '</div>';

                    $("#advanceBillRows").append(addadvanceBillRows);

                    filter_count++;
                    $('#count_of_bill').val(filter_count);
                }

                $("#advanceBillRows").on('click', '.remove', function(e) {
                    e.preventDefault();
                    $(this).parent().remove();
                    if (filter_count == 1) {
                        filter_count = 1;
                        $('#advanceFilterInfo').text("");
                    } else {
                        filter_count--;
                        $('#advanceFilterInfo').text("(no.of filter : " + (filter_count) + ")");
                    }
                    $('#count_of_bill').val(filter_count);
                });

                function clearAllBill(isfilterclear = 0) {
                    var deferred = $.Deferred();
                    filter_count = 1;
                    $('#count_of_bill').val(filter_count);
                    $('#lead_filter_value_0').val('');
                    $("#advanceBillRows").html("");
                    // $('#advanceFilterInfo').text("");
                    $('#lead_filter_text_field_div_0').show();
                    $('#lead_filter_div_0').hide();
                    $('#lead_filter_multi_div_0').hide();
                    $('#lead_filter_date_picker_div_0').hide();
                    $('#lead_filter_fromto_date_picker_div_0').hide();
                    if (isfilterclear == 1) {
                        $('#advance-bill-view').html(
                            '<div><label class="star-radio d-flex align-items-center justify-content-between"><span>Select View</span><i class="bx bxs-right-arrow"></i></label></div>'
                            );
                    } else {
                        deferred.resolve();
                    }
                    ischeckBill();
                    return deferred.promise();
                }

                $("#btnClearBill").click(function(event) {
                    event.preventDefault();
                    clearAllBill(1);
                });

                function ischeckBill(isfilter = 0) {
                    if (filter_count > 1) {
                        $('#isbillcount').show();
                        $('#isbillcount').text(filter_count);

                    } else if (filter_count == 1) {
                        var bill_number = $('#bill_number_0');
                        var lead_file_file_name = $('#lead_file_file_name_0');
                    } else {
                        if (isfilter == 1) {
                            $('#isbillcount').show();
                            $('#isbillcount').text(filter_count);
                        } else {
                            $('#isbillcount').hide();
                            $('#isbillcount').text(filter_count);
                        }
                    }
                    $("#saveBillFilter").html('Save');
                }

                $('#saveBillFilter').on('click', function() {
                    $("#saveBillFilter").html(
                        '<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span  >Saving...</span>');
                    $('#hidden_status').attr('value', 0);
                    // saveLogWithAdvanceFilterList();
                    reloadBillList(0, 1);
                    saveProcess();
                    ischeckFilter();
                })

                function reloadBillList(status = 0) {
                    // leadtable.settings()[0].jqXHR.abort();
                    if (status != 0) {
                        clearAllBill(0);
                        $('#advance-bill-view').html(
                            '<div><label class="star-radio d-flex align-items-center justify-content-between"><span>Select View</span><i class="bx bxs-right-arrow"></i></label></div>'
                        );
                        isLeadAmountSummaryRefresh = 0;
                        $('#hidden_status').attr('value', status);
                    }

                    let tempadvanceBillList = [];

                    tempadvanceBillList.push({
                        lead_file_file_name: $('#lead_file_file_name_0').val(),
                        bill_number: $('#bill_number_0').val(),
                    });

                    $('#advanceBillRows input[name="multi_bill_loop"]').each(function(ind) {
                        let filtValId = $(this).attr("filt_id");
                        // console.log(filtValId);
                        tempadvanceBillList.push({
                            bill_number: $('#bill_number_0' + filtValId).val(),
                            lead_file_file_name: $('#lead_file_file_name_0' + filtValId).val(),
                        });
                    });

                    advanceBillList.push(tempadvanceBillList) = JSON.stringify(tempadvanceBillList)
                }
            });
            
            function RefreshStatus(lead_id, lead_status) {
                $.ajax({
                    type: 'GET',
                    url: ajaxURLRefreshStatusFunnel + "?lead_id=" + lead_id,
                    success: function(resultData) {
                        if (resultData['status'] == 1) {
                            $("#detail_status_funnel").html(resultData['view']);
                        }
                    }
                });

                $('#funnel_status_bar').empty();
                $('#funnel_status_bar').load(window.location.href + ' .lead_status_filter_remove ');

                $('#' + lead_id + '_lead_list_status').text(lead_status);
            }

            function Load_Tooltip_Action() {
                $(".closing-badge").mouseover(function(e) {
                    var $tip = $(this).next();
                    var $arrow = $(this).next().find(".tip_arrow");

                    $tip.css("display", "block");
                    $tip.css("left", $(this).position().left - 9 + "px");
                    $tip.css("top", $(this).position().top + this.offsetHeight + 14 + "px");
                    $arrow.css("top", 0);
                    $arrow.css("left", this.offsetWidth * 1 / 2 + 2 + "px");
                    $arrow.css({
                        "border-bottom-color": "#bbbefcf0",
                        "margin-top": "-20px",
                        "border-top-color": "transparent"
                    });
                });
                $(".closing-badge").mouseleave(function(e) {
                    var $tip = $(this).next();
                    $tip.css("display", "none");
                });
                $(".closing-badge1").mouseover(function(e) {
                    var $tip = $(this).next();
                    var $arrow = $(this).next().find(".tip_arrow1");
                    $tip.css("display", "block");
                    $tip.css("left", $(this).position().left - 9 + "px");
                    $tip.css("top", $(this).position().top + this.offsetHeight + 14 + "px");
                    $arrow.css("top", 0);
                    $arrow.css("left", this.offsetWidth * 1 / 2 + 2 + "px");
                    $arrow.css({
                        "border-bottom-color": "#bbbefcf0",
                        "margin-top": "-20px",
                        "border-top-color": "transparent"
                    });
                });
                $(".closing-badge1").mouseleave(function(e) {
                    var $tip = $(this).next();
                    $tip.css("display", "none");
                });
            }

            $(function() {
                $('.datetimepicker').datetimepicker({
                    format: 'dd:mm:yyyy HH:ss a'
                });
            });

            $("#lead_status_new").select2();

            $("#lead_contact_tag_id").select2({
                ajax: {
                    url: ajaxURLSearchContactTag,
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
                placeholder: 'Search for tag',
                dropdownParent: $("#modalLeadContact .modal-content")
            });

            $("#lead_file_tag_id").select2({
                ajax: {
                    url: ajaxURLSearchFileTag,
                    dataType: 'json',
                    delay: 0,
                    data: function(params) {
                        return {
                            q: params.term, // search term
                            page: params.page,
                            status: function() {
                                return status;
                            },
                            arc_prime: function() {
                                return arc_prime;
                            },
                            tag_id: function() {
                                return tag_id;
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
                placeholder: 'Search for tag',
                dropdownParent: $("#modalLeadFile .modal-content")
            });

            $("#lead_meeting_city_id").select2({
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

            // A $( document ).ready() block.

            $(document).ready(function() {

                var newHeight1 = $(window).height() - $('#top-menu-lead').outerHeight() - 150;
                var newHeight2 = $(window).height() - $('#top-menu-lead').outerHeight();

                $('.lead-custom-scroll-1').css("max-height", newHeight1);
                $('.lead-custom-scroll-2').css("max-height", newHeight2);

                var isdetailload = 0;
                listAmountSummaryRefresh();
            });

            $(document).ready(function() {
                // Add click event handler to btnSaveFile button
                $('#btnSaveFile').on('click', function(event) {
                    // Prevent the default form submission behavior
                    event.preventDefault();
                    // Submit the form using jQuery submit() function
                    $('#formLeadFile').submit();
                });
            });

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
                $('#formLead').ajaxForm(options);
                $('#formLeadContact').ajaxForm(options);
                $('#formLeadFile').ajaxForm(options);
                $("#formLeadCall").ajaxForm(options);
                $("#formLeadTask").ajaxForm(options);
                $("#formLeadMeeting").ajaxForm(options);
                $("#formLeadQuotation").ajaxForm(options);
                $("#formAutogenerateAction").ajaxForm(options);
                $("#formLeadStatusMove").ajaxForm(options);
                // $("#formLeadStatusChange").ajaxForm(options);
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

            // post-submit callback
            function showResponse(responseText, statusText, xhr, $form) {

                $(".save-btn").html("Save");
                $(".save-btn").prop("disabled", false);

                if ($form[0]['id'] == "formLead") {
                    if (responseText['status'] == 1) {
                        toastr["success"](responseText['msg']);
                        $('#formLead').trigger("reset");
                        $("#modalLead").modal('hide');
                        if ($('#isfiltercount').text() > 1) {
                            reloadLeadList(0, 1);
                        } else {
                            reloadLeadList();
                        }
                        loadDetail = 0;

                        getDataDetail(responseText['id']);

                    } else if (responseText['status'] == 0) {

                        if (typeof responseText['data'] !== "undefined") {

                            var size = Object.keys(responseText['data']).length;
                            if (size > 0) {

                                for (var [key, value] of Object.entries(responseText['data'])) {

                                    $('#phone_no_error_dialog').show();
                                    $('#error_text').text(responseText['msg']);
                                }

                            }

                        } else {
                            $('#phone_no_error_dialog').show();
                            $('#error_text').text(responseText['msg']);
                        }

                    }

                } else if ($form[0]['id'] == "formLeadContact") {

                    if (responseText['status'] == 1) {
                        $('#contact_loader').show();
                        toastr["success"](responseText['msg']);
                        $('#formLeadContact').trigger("reset");
                        $("#modalLeadContact").modal('hide');

                        $.ajax({
                            type: 'GET',
                            url: ajaxURLContactALL + "?lead_id=" + responseText['id'] + "&islimit=1",
                            success: function(resultData) {
                                if (resultData['status'] == 1) {
                                    $("#tab_contact").html(resultData['view']);
                                    $('#contact_loader').hide();
                                }
                            }
                        });
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
                        $('#contact_loader').hide();

                    }

                } else if ($form[0]['id'] == "formLeadFile") {
                    if (responseText['status'] == 1) {
                        $('#file_loader').show();
                        toastr["success"](responseText['msg']);
                        $('#formLeadFile').trigger("reset");
                        $("#modalLeadFile").modal('hide');


                        $.ajax({
                            type: 'GET',
                            url: ajaxURLFileALL + "?lead_id=" + responseText['id'] + "&islimit=1",
                            success: function(resultData) {
                                if (resultData['status'] == 1) {
                                    $("#tab_files").html(resultData['view']);
                                    $('#file_loader').hide();
                                    if (resultData['files'] != '') {
                                        $.each(resultData['files'], function(index, value) {
                                            if (value['file_tag_id'] == 3) {
                                                // $('#bill_attached_btn').show();
                                                $('#reward_btn').show();
                                                // $("#bill_attached_btn a").attr('href', value['download']);
                                                tag_id = 3;
                                                return false;
                                            } else {
                                                // $('#bill_attached_btn').hide();
                                                $('#reward_btn').hide();
                                                // $("#bill_attached_btn a").attr('href', '');
                                                tag_id = 0;
                                            }
                                        })

                                        if (resultData['active_bill_count'] == 0) {
                                            tag_id = 0;
                                        } else {
                                            tag_id = 3;
                                        }
                                    } else {
                                        tag_id = 0;
                                    }
                                    // getDataDetail(responseText['id']);
                                }
                            }
                        });

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
                        $('#file_loader').hide();

                    }

                } else if ($form[0]['id'] == "formLeadCall") {

                    if (responseText['status'] == 1) {
                        toastr["success"](responseText['msg']);
                        $('#formLeadCall').trigger("reset");
                        $("#modalCall").modal('hide');
                        $("#lead_call_move_to_close").val(0);
                        if (responseText['is_action'] == 0) {
                            $('#open_action_loader').show();
                            $.ajax({
                                type: 'GET',
                                url: ajaxURLOpenActionAll + "?lead_id=" + responseText['id'],
                                success: function(resultData) {
                                    if (resultData['status'] == 1) {
                                        $("#tab_action").html(resultData['view']);
                                        $('#open_action_loader').hide();
                                    }
                                }
                            });
                        } else if (responseText['is_action'] == 1) {
                            $('#open_action_loader').show();
                            $('#close_action_loader').show();
                            $.ajax({
                                type: 'GET',
                                url: ajaxURLOpenActionAll + "?lead_id=" + responseText['id'],
                                success: function(resultData) {
                                    if (resultData['status'] == 1) {
                                        $("#tab_action").html(resultData['view']);
                                        $('#open_action_loader').hide();
                                    }
                                }
                            });

                            $.ajax({
                                type: 'GET',
                                url: ajaxURLCloseActionALL + "?lead_id=" + responseText['id'],
                                success: function(resultData) {
                                    if (resultData['status'] == 1) {
                                        $("#tab_close_action").html(resultData['view']);
                                        $('#close_action_loader').hide();
                                        RefreshStatus(resultData['lead_id'], resultData['lead_status']);
                                    }
                                }
                            });
                        }

                        $('#note_loader').show();
                        $.ajax({
                            type: 'GET',
                            url: ajaxURLUpdateALL + "?lead_id=" + responseText['id'] + "&islimit=1",
                            success: function(resultData) {
                                if (resultData['status'] == 1) {
                                    $("#tab_notes").html(resultData['view']);
                                    $('#note_loader').hide();
                                }
                            }
                        });

                        if (responseText['ask_for_status_change'] == 1) {
                            $("#modalStatus").modal('show');
                            $("#lead_status_lead_id").val(responseText['id']);



                            for (var i = 0; i < responseText['status_array'].length; i++) {



                                var newOption = new Option(responseText['status_array'][i]['name'], responseText['status_array']
                                    [i]['id'], false, false);
                                $('#lead_status_new').append(newOption).trigger('change');



                            }

                            // $("#formLeadStatusChange .loadingcls").hide();



                        }



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
                        $('#close_action_loader').hide();
                        $('#open_action_loader').hide();
                    }

                } else if ($form[0]['id'] == "formLeadTask") {

                    if (responseText['status'] == 1) {
                        toastr["success"](responseText['msg']);
                        $('#formTaskCall').trigger("reset");
                        $("#modalTask").modal('hide');
                        if (responseText['lead_task_auto_generate'] == 1 && responseText['user_type'] == 9) {
                            $("#lead_call_autogenerate").val(1);
                            $("#lead_call_ref_id").val(responseText['task_id']);
                            $("#modalCall").modal('show');
                            $("#formLeadCall .loadingcls").hide();
                            $("#lead_call_id").val(0);
                            $('#callFooter1 .save-btn').show();
                            $('#callFooter1 .save-btn').removeClass('d-none');
                            $('#call_close_cross_btn').addClass('d-none');
                            $('#call_close_btn').addClass('d-none');
                            $("#lead_call_move_to_close_btn").hide();
                            $('#modalCallLabel').text('Call');
                            $("#lead_call_move_to_close").val(0);
                            $("#lead_call_lead_id").val(responseText['id']);
                        }
                        $("#lead_task_move_to_close").val(0);
                        $("#lead_task_move_to_close_btn").hide();
                        $('#modalTaskLabel').text('Schedule Task');
                        $('#taskfooter1 .save-btn').show();
                        if (responseText['is_action'] == 0) {
                            $('#open_action_loader').show();
                            $.ajax({
                                type: 'GET',
                                url: ajaxURLOpenActionAll + "?lead_id=" + responseText['id'],
                                success: function(resultData) {
                                    if (resultData['status'] == 1) {
                                        $("#tab_action").html(resultData['view']);
                                        $('#open_action_loader').hide();
                                    }
                                }
                            });

                        } else if (responseText['is_action'] == 1) {
                            $('#open_action_loader').show();
                            $('#close_action_loader').show();
                            $.ajax({
                                type: 'GET',
                                url: ajaxURLOpenActionAll + "?lead_id=" + responseText['id'],
                                success: function(resultData) {
                                    if (resultData['status'] == 1) {
                                        $("#tab_action").html(resultData['view']);
                                        $('#open_action_loader').hide();
                                    }
                                }
                            });

                            $.ajax({
                                type: 'GET',
                                url: ajaxURLCloseActionALL + "?lead_id=" + responseText['id'],
                                success: function(resultData) {
                                    if (resultData['status'] == 1) {
                                        $("#tab_close_action").html(resultData['view']);
                                        $('#close_action_loader').hide();
                                        RefreshStatus(resultData['lead_id'], resultData['lead_status']);
                                    }
                                }
                            });

                        }
                        $('#note_loader').show();
                        $.ajax({
                            type: 'GET',
                            url: ajaxURLUpdateALL + "?lead_id=" + responseText['id'] + "&islimit=1",
                            success: function(resultData) {
                                if (resultData['status'] == 1) {
                                    $("#tab_notes").html(resultData['view']);
                                    $('#note_loader').hide();
                                }
                            }
                        });


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
                        $('#close_action_loader').hide();
                        $('#open_action_loader').hide();
                    }

                } else if ($form[0]['id'] == "formLeadMeeting") {

                    if (responseText['status'] == 1) {
                        toastr["success"](responseText['msg']);
                        $('#formLeadMeeting').trigger("reset");
                        $("#modalMeeting").modal('hide');
                        if (responseText['is_action'] == 0) {
                            $('#open_action_loader').show();
                            $.ajax({
                                type: 'GET',
                                url: ajaxURLOpenActionAll + "?lead_id=" + responseText['id'],
                                success: function(resultData) {
                                    if (resultData['status'] == 1) {
                                        $("#tab_action").html(resultData['view']);
                                        $('#open_action_loader').hide();
                                    }
                                }
                            });
                        } else if (responseText['is_action'] == 1) {
                            $('#open_action_loader').show();
                            $('#close_action_loader').show();
                            $.ajax({
                                type: 'GET',
                                url: ajaxURLOpenActionAll + "?lead_id=" + responseText['id'],
                                success: function(resultData) {
                                    if (resultData['status'] == 1) {
                                        $("#tab_action").html(resultData['view']);
                                        $('#open_action_loader').hide();
                                    }
                                }
                            });

                            $.ajax({
                                type: 'GET',
                                url: ajaxURLCloseActionALL + "?lead_id=" + responseText['id'],
                                success: function(resultData) {
                                    if (resultData['status'] == 1) {
                                        $("#tab_close_action").html(resultData['view']);
                                        $('#close_action_loader').hide();
                                        RefreshStatus(resultData['lead_id'], resultData['lead_status']);
                                    }
                                }
                            });
                        }
                        $('#note_loader').show();
                        $.ajax({
                            type: 'GET',
                            url: ajaxURLUpdateALL + "?lead_id=" + responseText['id'] + "&islimit=1",
                            success: function(resultData) {
                                if (resultData['status'] == 1) {
                                    $("#tab_notes").html(resultData['view']);
                                    $('#note_loader').hide();
                                }
                            }
                        });


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
                        $('#close_action_loader').hide();
                        $('#open_action_loader').hide();
                    }

                } else if ($form[0]['id'] == "formLeadQuotation") {

                    if (responseText['status'] == 1) {
                        toastr["success"](responseText['msg']);
                        $('#formLeadQuotation').trigger("reset");
                        $("#modalQuotation").modal('hide');
                        if ($('#isfiltercount').text() > 1) {
                            reloadLeadList(0, 1);
                        } else {
                            reloadLeadList();
                        }
                        viewLeadId == null;

                        var url = "{{ route('crm.deal') }}" + "?id=" + responseText['id'];
                        window.open(url);
                        // getDataDetail(responseText['id']);


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

                } else if ($form[0]['id'] == "formAutogenerateAction") {

                    if (responseText['status'] == 1) {
                        toastr["success"](responseText['msg']);
                        $('#formAutogenerateAction').trigger("reset");
                        $("#modalAutoScheduleCall").modal('hide');
                        $("#lead_auto_call_move_to_close").val(0);
                        if (responseText['is_action'] == 0) {
                            $('#open_action_loader').show();
                            $.ajax({
                                type: 'GET',
                                url: ajaxURLOpenActionAll + "?lead_id=" + responseText['id'],
                                success: function(resultData) {
                                    if (resultData['status'] == 1) {
                                        $("#tab_action").html(resultData['view']);
                                        $('#open_action_loader').hide();
                                    }
                                }
                            });
                        } else if (responseText['is_action'] == 1) {
                            $('#open_action_loader').show();
                            $('#close_action_loader').show();
                            $.ajax({
                                type: 'GET',
                                url: ajaxURLOpenActionAll + "?lead_id=" + responseText['id'],
                                success: function(resultData) {
                                    if (resultData['status'] == 1) {
                                        $("#tab_action").html(resultData['view']);
                                        $('#open_action_loader').hide();
                                    }
                                }
                            });

                            $.ajax({
                                type: 'GET',
                                url: ajaxURLCloseActionALL + "?lead_id=" + responseText['id'],
                                success: function(resultData) {
                                    if (resultData['status'] == 1) {
                                        $("#tab_close_action").html(resultData['view']);
                                        $('#close_action_loader').hide();
                                    }
                                }
                            });
                        }

                        $('#note_loader').show();
                        $.ajax({
                            type: 'GET',
                            url: ajaxURLUpdateALL + "?lead_id=" + responseText['id'] + "&islimit=1",
                            success: function(resultData) {
                                if (resultData['status'] == 1) {
                                    $("#tab_notes").html(resultData['view']);
                                    $('#note_loader').hide();
                                }
                            }
                        });

                        if (responseText['ask_for_status_change'] == 1) {
                            $("#modalStatus").modal('show');
                            $("#lead_status_lead_id").val(responseText['id']);



                            for (var i = 0; i < responseText['status_array'].length; i++) {



                                var newOption = new Option(responseText['status_array'][i]['name'], responseText['status_array']
                                    [i]['id'], false, false);
                                $('#lead_status_new').append(newOption).trigger('change');



                            }

                            // $("#formLeadStatusChange .loadingcls").hide();



                        }



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
                        $('#close_action_loader').hide();
                        $('#open_action_loader').hide();
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
                } else if ($form[0]['id'] == "formLeadStatusMove") {
                    if (responseText['status'] == 1) {
                        toastr["success"](responseText['msg']);
                        $('#modalStatusMove').modal('hide');
                        saveDetailUpdate(responseText['lead_id'], 1, "" + responseText['data'] + "");
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
                }

            }


            var firstPageLeadId = 0;
            var loadDetail = 1;
            var firstTimeDetailLoad = 1;


            function getDataDetail(id) {

                Lead_point_id = id;

                if (id != 0 && id != null && id != undefined) {
                    if (previousLeadDetailRequest) {
                        previousLeadDetailRequest.abort();
                    }

                    $("#lead_" + id).parent().parent().addClass('active_lead');
                    isdetailload = 0;
                    $('#list_data_loader').show();

                    var scrollTopHeightDataTable = $('#lead_detail .lead-custom-scroll-2').prop('scrollTop');


                    previousLeadDetailRequest = $.ajax({
                        type: 'GET',
                        url: ajaxURLDataDatail + "?id=" + id,
                        success: function(resultData) {
                            if (resultData['status'] == 1) {
                                $("#lead_detail").html(resultData['view']);
                                Load_Tooltip_Action();
                                $(".lead_li").parent().parent().removeClass('active_lead');
                                $("#lead_" + id).parent().parent().addClass('active_lead');

                                if (firstTimeDetailLoad == 0) {

                                    $("#lead_detail .lead-custom-scroll-2").animate({
                                        scrollTop: scrollTopHeightDataTable
                                    }, 10);

                                } else {
                                    firstTimeDetailLoad = 0;
                                    var newHeight2 = $(window).height() - $('#top-menu-lead').outerHeight() - 150 -
                                        50;
                                    $('.lead-custom-scroll-2').css("max-height", newHeight2);

                                }

                                $('#point_claimed_count').text(resultData['data']['LeadBillSummary_claimed']);
                                $('#point_query_count').text(resultData['data']['LeadBillSummary_query']);
                                $('#point_lapsed_count').text(resultData['data']['LeadBillSummary_laps']);

                                var data = resultData['data']['lead'];
                                $('#' + data['id'] + '_lead_list_status').text(data['status_label']);
                                // _lead_list_status
                                // var newOption = new Option(data['source']['text'], data['source']['id'], false, false);
                                // $('#lead_detail_source').append(newOption).trigger('change');
                                // $("#lead_detail_source").val("" + data['source']['id'] + "");
                                // $('#lead_detail_source').trigger('change');

                                var newOption = new Option(data['source_type'], data['source_type_id'], false,
                                    false);
                                $('#lead_detail_source_type').append(newOption).trigger('change');
                                $("#lead_detail_source_type").val("" + data['source_type_id'] + "");
                                $('#lead_detail_source_type').trigger('change');


                                var newOption = new Option(data['site_type']['text'], data['site_type']['id'],
                                    false, false);
                                $('#lead_detail_site_type').append(newOption).trigger('change');
                                $("#lead_detail_site_type").val("" + data['site_type']['id'] + "");
                                $('#lead_detail_site_type').trigger('change');
                                
                                var newOption = new Option(data['site_stage']['text'], data['site_stage']['id'],
                                    false, false);
                                $('#lead_detail_site_stage').append(newOption).trigger('change');
                                $("#lead_detail_site_stage").val("" + data['site_stage']['id'] + "");
                                $('#lead_detail_site_stage').trigger('change');
                                
                                
                                if (data['tag'].length > 0) {
                                    $("#lead_detail_tag").empty().trigger('change');
                                    var selectedSalePersons = [];
                                    for (var i = 0; i < data['tag'].length; i++) {
                                        selectedSalePersons.push('' + data['tag'][i]['id'] + '');
                                        var newOption = new Option(data['tag'][i]['text'], data['tag'][i]['id'],
                                            false, false);
                                        $('#lead_detail_tag').append(newOption).trigger('change');
                                    }
                                    $("#lead_detail_tag").val(selectedSalePersons).change();
                                }

                                var newOption = new Option(data['sub_status']['text'], data['sub_status']['id'],
                                    false, false);
                                    $('#lead_detail_sub_status').append(newOption).trigger('change');
                                    $("#lead_detail_sub_status").val("" + data['sub_status']['id'] + "");
                                    $('#lead_detail_sub_status').trigger('change');
                                    
                                if (data['source_type_id'] != null && data['source_type_id'] != "") {
                                    var selectedtype = data['source_type_id'];
                                    if(data['is_editable'] == 0){
                                        $('#lead_detail_site_type').prop('disabled', true);
                                        $("#lead_detail_source_text").prop('disabled', true);
                                        $("#lead_detail_source").prop('disabled', true);
                                    }

                                    if (selectedtype.split("-")[0] == "textrequired") {
                                        $("#lead_detail_source_text").show();
                                        $("#lead_detail_source_text").val(data['source']['text']);
                                        $("#div_lead_detail_source").hide();

                                        $("#lead_detail_source_text").prop('required', true);

                                        $("#lead_detail_source").removeAttr('required');

                                    } else if (selectedtype.split("-")[0] == "textnotrequired") {
                                        $("#lead_detail_source_text").show();
                                        $("#lead_detail_source_text").val(data['source']['text']);
                                        $("#div_lead_detail_source").hide();

                                        $("#lead_detail_source_text").removeAttr('required');
                                        $("#lead_detail_source").removeAttr('required');
                                    } else if (selectedtype.split("-")[0] == "fix") {
                                        $("#lead_detail_source_text").show();
                                        $("#lead_detail_source_text").val(data['source']['text']);
                                        $("#div_lead_detail_source").hide();

                                        $("#lead_detail_source_text").prop('readonly', true);
                                        $("#lead_detail_source_text").val('-');

                                        $("#lead_detail_source_text").removeAttr('required');
                                        $("#lead_detail_source").removeAttr('required');
                                    } else {
                                        $("#lead_detail_source_text").hide();
                                        $("#div_lead_detail_source").show();

                                        $("#lead_detail_source").prop('required', true);

                                        $("#lead_detail_source_text").removeAttr('required');

                                        var newOption = new Option(data['source']['text'], data['source']['id'],
                                            false,
                                            false);
                                        $('#lead_detail_source').append(newOption).trigger('change');
                                        $("#lead_detail_source").val("" + data['source']['id'] + "");
                                        $('#lead_detail_source').trigger('change');


                                    }
                                }

                                var contact = resultData['data']['contacts'];
                                if (contact != '') {
                                    $.each(contact, function(index, value) {
                                        if (value['type'] == 202 || value['type'] == 302) {
                                            arc_prime = 1;
                                            return false;
                                        } else {
                                            arc_prime = 0;
                                        }
                                    })
                                }

                                status = resultData['data']['lead']['status'];
                                isdetailload = 1;

                                var file = resultData['data']['files'];
                                if (file != '') {
                                    $.each(file, function(index, value) {
                                        console.log(value['file_tag_id']);
                                        if (value['file_tag_id'] == 3) {
                                            // $('#bill_attached_btn').show();
                                            $('#reward_btn').show();
                                            // $("#bill_attached_btn a").attr('href', value['download']);
                                            tag_id = 3;
                                            return false;
                                        } else {
                                            // $('#bill_attached_btn').hide();
                                            $('#reward_btn').hide();
                                            // $("#bill_attached_btn a").attr('href', '');
                                            tag_id = 0;
                                        }
                                    })
                                } else {
                                    tag_id = 0;
                                }
                                if (resultData['data']['active_bill_count'] == 0) {
                                    tag_id = 0;
                                } else {
                                    tag_id = 3;
                                }
                                $('#list_data_loader').hide();
                            }
                        }
                    })
                }
            }


            function addLeadContactModal(id) {



                $("#modalLeadContact").modal('show');
                $("#formLeadContact .loadingcls").hide();
                $('#formLeadContact').trigger("reset");
                $("#lead_contact_lead_id").val(id);

                $("#lead_contact_id").val(0);
                $('#lead_contact_tag_id').empty().trigger('change');


            }

            function viewAllLeadContact(id) {
                $('#contact_loader').show();

                var scrollTopHeightDataTable = $('#lead_detail .lead-custom-scroll-2').prop('scrollTop');
                $.ajax({
                    type: 'GET',
                    url: ajaxURLContactALL + "?lead_id=" + id + "&islimit=0",
                    success: function(resultData) {
                        if (resultData['status'] == 1) {
                            $("#tab_contact").html(resultData['view']);
                            $('#contact_loader').hide();

                            $("#lead_detail .lead-custom-scroll-2").animate({
                                scrollTop: scrollTopHeightDataTable
                            }, 10);
                        }
                    }
                });

            }

            function viewAllLeadFiles(id) {

                $('#file_loader').show();
                var scrollTopHeightDataTable = $('#lead_detail .lead-custom-scroll-2').prop('scrollTop');
                $.ajax({
                    type: 'GET',
                    url: ajaxURLFileALL + "?lead_id=" + id + "&islimit=0",
                    success: function(resultData) {
                        if (resultData['status'] == 1) {
                            $("#tab_files").html(resultData['view']);
                            if (resultData['files'] != '') {
                                $.each(resultData['files'], function(index, value) {
                                    if (value['file_tag_id'] == 3) {
                                        // $('#bill_attached_btn').show();
                                        $('#reward_btn').show();
                                        // $("#bill_attached_btn a").attr('href', /value['download']);
                                        tag_id = 3;
                                        return false;
                                    } else {
                                        // $('#bill_attached_btn').hide();
                                        $('#reward_btn').hide();
                                        // $("#bill_attached_btn a").attr('href', '');
                                        tag_id = 0;
                                    }

                                })

                                if (resultData['active_bill_count'] == 0) {
                                    tag_id = 0;
                                } else {
                                    tag_id = 3;
                                }
                            } else {
                                tag_id = 0;
                            }
                            $('#file_loader').hide();
                            $("#lead_detail .lead-custom-scroll-2").animate({
                                scrollTop: scrollTopHeightDataTable
                            }, 10);
                        }
                    }
                });

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

            function addLeadFileModal(id) {
                $("#advanceBillRows").hide();
                $("#lead_file_lead_id").val(id);
                $("#modalLeadFile").modal('show');
                $("#formLeadFile .loadingcls").hide();
                resetForm();
            }

            function resetForm() {
                document.getElementById("formLeadFile").reset(); // Reset the form
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

            function changeAddActionType(id) {
                var addActionType = $("#add_action_type").val();
                $("#add_action_type").val('0');
                if (addActionType == "1") {
                    $('#lead_call_autogenerate').val(0);
                    $('#lead_call_ref_id').val(0);
                    $('#add_info_div').addClass('d-none');
                    $('#call_received_but_reschedule_div').addClass('d-none');
                    $('#call_close_cross_btn').removeClass('d-none');
                    $('#call_close_btn').removeClass('d-none');


                    $('#call_notes_label').text('Call Notes')
                    $('#lead_call_call_description').attr('placeholder', 'Call Notes');

                    $('#lead_call_closing_note_div').addClass('d-none');
                    $('#lead_call_reminder_div').removeClass('d-none');
                    $('#lead_call_purpose_div').removeClass('d-none');
                    $('#lead_call_status_div').addClass('d-none');
                    $('#lead_call_outcome_div').addClass('d-none');

                    $('#formLeadCall').trigger("reset");
                    $('#lead_call_type_id').empty().trigger('change');
                    $('#lead_call_contact_name').empty().trigger('change');
                    $('#lead_call_call_outcome').empty().trigger('change');
                    $('#lead_call_status').empty().trigger('change');

                    $("#modalCall").modal('show');
                    $("#formLeadCall .loadingcls").hide();
                    $("#lead_call_lead_id").val(id);
                    $("#lead_call_id").val(0);
                    $('#callFooter1 .save-btn').show();
                    $('#callFooter1 .save-btn').removeClass('d-none');
                    $("#lead_call_move_to_close_btn").hide();
                    $('#modalCallLabel').text('Call');
                    $("#lead_call_move_to_close").val(0);

                    var newOption = new Option("SELF", "0", false, false);
                    $('#lead_call_assign_user').append(newOption).trigger('change');
                    $("#lead_call_assign_user").val("" + "0" + "");
                    $('#lead_call_assign_user').trigger('change');

                    $('#lead_call_assign_user_div, #select2-lead_call_assign_user-container, #lead_call_type_div, #lead_call_contact_name_div, #lead_call_call_schedule_div, #lead_call_reminder_div, #lead_call_purpose_div, #lead_call_notes_div, #select2-lead_call_type_id-container, #lead_call_call_schedule, #select2-lead_call_contact_name-container, #lead_call_call_description, #lead_call_purpose, #lead_call_reminder, #select2-lead_call_reminder_date_time-container, #lead_call_schedule_date, #select2-lead_call_schedule_time-container')
                        .removeClass('bg-light')
                    $('#lead_call_call_schedule, #lead_call_reminder, #lead_call_call_description').attr('readonly', false);
                    $('#pointer_event_call_assign_user, #pointer_event_call_type, #pointer_event_call_contact_name, #lead_call_call_schedule_div, #lead_call_reminder_div')
                        .removeClass('pe-none');

                } else if (addActionType == "2") {
                    is_meeting_schedule = 0;

                    $('#lead_meeting_description_label').text('Meeting Notes');
                    $('#lead_meeting_description').attr('placeholder', 'Meeting Notes');
                    $('#lead_meeting_is_notification_div').removeClass('d-none');

                    $('#formLeadMeeting').trigger("reset");
                    $('#lead_meeting_title_id').empty().trigger('change');
                    $('#lead_meeting_participants').empty().trigger('change');
                    $('#lead_meeting_type_id').empty().trigger('change');
                    $('#lead_meeting_meeting_outcome').empty().trigger('change');
                    $('#lead_meeting_status').empty().trigger('change');

                    $("#modalMeeting").modal('show');
                    $("#formLeadMeeting .loadingcls").hide();
                    $("#lead_meeting_lead_id").val(id);
                    $("#lead_meeting_id").val(0);
                    $("#lead_meeting_move_to_close_btn").hide();
                    $('#modalMeetingLabel').text('Set Up Meeting');
                    $('#meetingFooter1 .save-btn').show();
                    $('#meetingFooter1 .save-btn').removeClass('d-none');
                    $("#lead_meeting_move_to_close").val(0);

                    $('#lead_meeting_closing_note_div').addClass('d-none');
                    $('#lead_meeting_outcome_div').addClass('d-none');
                    $('#lead_meeting_status_div').addClass('d-none');
                    $('#lead_meeting_title_div, #lead_meeting_type_div, #lead_meeting_location_div, #lead_meeting_meeting_date_time_div, #lead_meeting_interval_time_div, #lead_meeting_is_notification_div, #lead_meeting_participants_div, #lead_meeting_note_div, #select2-lead_meeting_title_id-container, #select2-lead_meeting_type_id-container, #lead_meeting_location, #lead_meeting_meeting_date_time, #lead_meeting_reminder_id, #lead_meeting_description, #select2-lead_meeting_reminder_date_time-container, #lead_meeting_date, #select2-lead_meeting_time-container')
                        .removeClass('bg-light')
                    $('#select2-lead_meeting_interval_time-container, #lead_meeting_participants_div .select2-selection--multiple')
                        .removeClass('bg-light');
                    $('#lead_meeting_location, #lead_meeting_meeting_date_time, #lead_meeting_reminder_id, #lead_meeting_description')
                        .attr('readonly', false);
                    $('#pointer_event_meeting_participants, #pointer_event_meeting_title, #pointer_event_meeting_type, #lead_meeting_meeting_date_time_div, #lead_meeting_interval_time_div, #lead_meeting_is_notification_div')
                        .removeClass('pe-none');

                    $('#meeting_date_schedule').addClass('d-none');
                    $('#suggested_time').addClass('d-none');
                    $('#meeting_form').addClass('col-12').removeClass('col-7');

                } else if (addActionType == "3") {

                    $('#formLeadTask').trigger("reset");
                    $("#lead_task_assign_to").empty().trigger('change');
                    $('#lead_task_task_outcome').empty().trigger('change');
                    $('#lead_task_status').empty().trigger('change');


                    $('#lead_status_div').addClass('d-none');
                    $('#lead_closing_note_div').addClass('d-none');
                    $('#lead_task_outcome_div').addClass('d-none');

                    $("#modalTask").modal('show');
                    $("#formLeadTask .loadingcls").hide();
                    $("#lead_task_lead_id").val(id);
                    $("#lead_task_id").val(0);
                    $("#lead_task_move_to_close_btn").hide();
                    $('#modalTaskLabel').text('Schedule Task');
                    $('#taskfooter1 .save-btn').show();
                    $('#taskfooter1 .save-btn').removeClass('d-none');
                    $("#lead_task_move_to_close").val(0);

                    var newOption = new Option("SELF", "0", false, false);
                    $('#lead_task_assign_to').append(newOption).trigger('change');
                    $("#lead_task_assign_to").val("" + "0" + "");
                    $('#lead_task_assign_to').trigger('change');
                    $('#lead_task_assign_to_div, #lead_task_div, #lead_task_due_date_time_div, #lead_task_reminder_div, #lead_task_description_div, #select2-lead_task_assign_to-container, #lead_task_task, #lead_task_due_date,#select2-lead_task_due_time-container,#select2-lead_task_reminder_date_time-container, #lead_task_reminder_id, #lead_task_description')
                        .removeClass('bg-light');
                    $('#lead_task_task, #lead_task_due_date_time, #lead_task_reminder_id, #lead_task_description').attr(
                        'readonly', false);
                    $('#pointer_event_assign_to, #lead_task_due_date_time_div, #lead_task_reminder_div').removeClass('pe-none');
                }
            }

            function deleteLeadFile(id) {

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
                                url: ajaxURLDeleteFile + "?id=" + id,
                                success: function(resultData) {

                                    if (resultData['status'] == 1) {

                                        $("#tr_file_" + id).remove();

                                        t()



                                    }




                                }
                            });



                        })
                    },
                }).then(function(t) {

                    if (t.value === true) {



                        Swal.fire({
                            title: "Mark as deleted!",
                            text: "Your record has been updated.",
                            icon: "success"
                        });


                    }

                });

            }

            function editLeadContact(id) {
                $("#modalLeadContact").modal('show');
                $("#formLeadContact .loadingcls").hide();
                // $("#lead_contact_id").val(id);
                // $("#lead_contact_lead_id").val(id);

                $.ajax({
                    type: 'GET',
                    url: ajaxURContactDetail + "?id=" + id,
                    success: function(responseText) {
                        if (responseText['status'] == 1) {
                            var newOption = new Option(responseText['data']['type']['text'], responseText['data'][
                                'type'
                            ]['id'], false, false);
                            $('#lead_contact_tag_id').append(newOption).trigger('change');
                            $("#lead_contact_tag_id").val("" + responseText['data']['type']['id'] + "");
                            $('#lead_contact_tag_id').trigger('change');
                            $("#lead_contact_id").val(responseText['data']['id']);
                            $("#lead_contact_lead_id").val(responseText['data']['lead_id']);
                            $("#lead_contact_first_name").val(responseText['data']['first_name']);
                            $("#lead_contact_last_name").val(responseText['data']['last_name']);
                            $("#lead_contact_phone_number").val(responseText['data']['phone_number']);
                            $("#lead_contact_alernate_phone_number").val(responseText['data'][
                                'alernate_phone_number'
                            ]);
                            $("#lead_contact_email").val(responseText['data']['email']);



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


            function uploadQuotation(id) {

                $("#modalQuotation").modal('show');
                $("#formLeadQuotation .loadingcls").hide();
                $("#lead_quotation_lead_id").val(id);


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

            $(function() {
                var sd = new Date();
                // $('#lead_call_call_schedule').datetimepicker({
                //     format: 'yyyy-m-d hh:mm:ss',
                // });
            });

            function changeFinalQuotation(quotid, quotgroupid) {
                $.ajax({
                    type: 'POST',
                    url: ajaxURQuotisFinalSave,
                    data: {
                        'quotid': quotid,
                        'quotgroupid': quotgroupid,
                        '_token': $("[name=_token]").val()
                    },
                    success: function(responseText) {
                        if (responseText['status'] == 1) {
                            isLeadAmountSummaryRefresh = 0;
                            if ($('#isfiltercount').text() > 1) {
                                reloadLeadList(0, 1);
                            } else {
                                reloadLeadList();
                            }
                            toastr["success"](responseText['msg']);
                        } else {
                            toastr["error"](responseText['msg']);
                        }


                    }
                });
            }

            function LeadViewLog(userId, user_type) {
                user_id = userId;
                user_type = user_type;
                Filter_type = 0;
                $("#modalLeadLog").modal('show');

                var LeadLogTable = $('#LeadLogTable').DataTable({
                    "aoColumnDefs": [{
                            "bSortable": false,
                            "aTargets": []
                        },
                        {
                            "bSearchable": false,
                            "aTargets": [2]

                        }
                    ],
                    "order": [
                        [0, 'desc']
                    ],
                    "processing": true,
                    "serverSide": true,
                    "bDestroy": true,
                    "pageLength": 10,
                    "ajax": {
                        "url": ajaxURLViewLeadLog,
                        "type": "POST",
                        "data": {
                            "_token": csrfToken,
                            "id": function() {
                                return user_id
                            },
                            "filter_type": function() {
                                return Filter_type
                            },
                            "user_type": function() {
                                return user_type
                            },
                            "from_date": function() {
                                return $('#start_date').val();
                            },
                            "to_date": function() {
                                return $('#end_date').val();
                            },
                            "data_view_type": function() {
                                return $('#data_view_type_val').val();
                            },
                        }
                    },
                    "aoColumns": [{
                            "mData": "id"
                        },
                        {
                            "mData": "name"
                        },
                        {
                            "mData": "status"
                        },
                        {
                            "mData": "quotation_amount"
                        },
                        {
                            "mData": "arc_and_ele_source"
                        },
                        {
                            "mData": "channel_partner"
                        },
                    ],
                });

                $('input[name="data_view_type"]').on('click', function() {
                    var data_view_type = $(this).val();
                    $('#data_view_type_val').val(data_view_type);
                    LeadLogTable.ajax.reload(null, false);
                });

                $('input[name="start_date"], input[name="end_date"]').on('change', function() {
                    var start_date = $('#start_date').val();
                    var end_date = $('#end_date').val();
                    LeadLogTable.ajax.reload(null, false);
                });

                LeadLogTable.on('xhr', function() {
                    var responseData = LeadLogTable.ajax.json();
                    if (responseData['user_type'] == "201") {
                        $('#user_type_column').text('Electrician')
                        $('#user_type_column1').text('Channel Partner')
                    } else if (responseData['user_type'] == "301") {
                        $('#user_type_column').text('Architect')
                        $('#user_type_column1').text('Channel Partner')
                    } else if (responseData['user_type'] == "101" || responseData['user_type'] == "102" || responseData[
                            'user_type'] == "103" || responseData['user_type'] == "104" || responseData['user_type'] ==
                        "105") {
                        $('#user_type_column').text('Architect')
                        $('#user_type_column1').text('Electrician')
                    }

                    $("#totalLead").html(responseData['TotalLeadCount']);
                    $("#totalRunningLead").html(responseData['RunningLeadCount']);
                    $("#totalWonLead").html(responseData['WonLeadCount']);
                    $("#totalRejectedLead").html(responseData['LostLeadCount']);
                    $("#modalLeadLogLabel").html(responseData['title']);
                    $('#totalLeadLogQuotationAmount').text(responseData['quotation_amount']);

                    $(".inquiry-log-active").removeClass("inquiry-log-active");

                    if (responseData['type'] == "0") {
                        $("#btnLeadLogTotal").addClass('inquiry-log-active');
                    } else if (responseData['type'] == "1") {
                        $("#btnLeadLogRunning").addClass('inquiry-log-active');
                    } else if (responseData['type'] == "2") {
                        $("#btnLeadLogWon").addClass('inquiry-log-active');
                    } else if (responseData['type'] == "3") {
                        $("#btnLeadLogLost").addClass('inquiry-log-active');
                    }
                });

                $("#btnLeadLogTotal").on('click', function() {
                    Filter_type = 0;
                    LeadLogTable.ajax.reload(null, false);
                });

                $("#btnLeadLogRunning").on('click', function() {
                    Filter_type = 1;
                    LeadLogTable.ajax.reload(null, false);
                });

                $("#btnLeadLogWon").on('click', function() {
                    Filter_type = 2;
                    LeadLogTable.ajax.reload(null, false);
                });

                $("#btnLeadLogLost").on('click', function() {
                    Filter_type = 3;
                    LeadLogTable.ajax.reload(null, false);
                });

                $("#applyFilter").on('click', function() {
                    LeadLogTable.ajax.reload(null, false);
                });
            }

            $('#saveAdvanceFilter').on('click', function() {
                // var isValid = true;

                // var selectColumn = $("#selectAdvanceFilterColumn_0");
                // if (!selectColumn.val() || selectColumn.val() == "0") {
                //     isValid = false;
                // }

                // var selectCondition = $("#selectAdvanceFilterCondtion_0");
                // if (!selectCondition.val() || selectCondition.val() == "0") {
                //     isValid = false;
                // }

                // var selectValue = $("#lead_filter_select_value_0");
                // if (!selectValue.val() || selectValue.val().length === 0) {
                //     isValid = false;
                // }

                // if (isValid) {
                $("#saveAdvanceFilter").html(
                    '<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i> <span  >Saving...</span>');
                $('#hidden_status').attr('value', 0);
                reloadLeadList(0, 1);
                ischeckFilter();
                // };
            })

            let advanceFilterList = '';
            var leadtable = $('#datatable').DataTable({
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
                        "_token": csrfToken,
                        "is_deal": is_deal,
                        'isAdvanceFilter': function() {
                            return $('#hidden_is_advancefilter').val();
                        },
                        'AdvanceData': function() {
                            return advanceFilterList;
                        },
                        "status": function() {
                            return $('#hidden_status').val();
                        }
                    }
                },
                "aoColumns": [{
                    "mData": "view"
                }, ],
                "pagingType": "simple_numbers",
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

            $.fn.DataTable.ext.pager.numbers_length = 6;
            $.fn.DataTable.ext.errMode = 'none';

            leadtable.on('xhr', function() {
                var responseData = leadtable.ajax.json();
                $('#list_record_count').html(responseData['recordsFiltered']);
                // $('#list_record_count').html('<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i>');


                var status = $('#hidden_status').val();
                $('.lead_status_filter_remove').removeClass("next-status-active-class");
                $('.lead_status_filter_' + status).addClass("next-status-active-class");

                if (viewLeadId == null || viewLeadId == "" || viewLeadId == undefined) {
                    getDataDetail(responseData['FirstPageLeadId']);
                } else {
                    getDataDetail(viewLeadId);
                }
                
                $('#list_data_loader').hide();
            });

            function reloadLeadList(status = 0, isAdvanceFilter = 0) {
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
                if (isAdvanceFilter == 1) {
                    isLeadAmountSummaryRefresh = 0;

                    $('#hidden_status').attr('value', 0);
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
                }

                advanceFilterList = JSON.stringify(tempadvanceFilterList)
                $('#list_record_count').html('<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i>');
                listAmountSummaryRefresh();
                // $('#datatable').DataTable().ajax.reload();

                leadtable.ajax.reload();
            }

            function listAmountSummaryRefresh() {
                if (isLeadAmountSummaryRefresh == 0) {

                    if (previousLeadListAmountSummaryRequest) {
                        previousLeadListAmountSummaryRequest.abort();
                    }
                    $('#total_whitelion_amt').html('<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i>');
                    $('#total_billing_amt').html('<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i>');
                    $('#total_quotation_amt').html('<i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i>');
                    previousLeadListAmountSummaryRequest = $.ajax({
                        type: 'POST',
                        url: ajaxURLDataListAmountSummaryAjax,
                        data: {
                            "_token": csrfToken,
                            "is_deal": is_deal,
                            'isAdvanceFilter': function() {
                                return $('#hidden_is_advancefilter').val();
                            },
                            'AdvanceData': function() {
                                return advanceFilterList;
                            },
                            "status": function() {
                                return $('#hidden_status').val();
                            }
                        },
                        success: function(responseText) {
                            if (responseText['status'] == 1) {
                                $('#total_whitelion_amt').html(responseText['data']['whitelion_amt']);
                                $('#total_billing_amt').html(responseText['data']['billing_amt']);
                                $('#total_quotation_amt').html(responseText['data']['total_amt']);
                                isLeadAmountSummaryRefresh == 1;

                            } else {
                                $('#total_whitelion_amt').html('0');
                                $('#total_billing_amt').html('0');
                                $('#total_quotation_amt').html('0');
                                toastr["error"](responseText['msg']);
                            }
                        },
                        error: function(request, error) {
                            $('#total_whitelion_amt').html('0');
                            $('#total_billing_amt').html('0');
                            $('#total_quotation_amt').html('0');
                        },
                    });
                }
            }


            $('#datatable_length').each(function() {
                @if ($data['is_deal'] == 1)
                    $(this).before(
                        '<div><i id="list_data_loader" class="bx bx-loader bx-spin font-size-16 align-middle me-2" style=""></i><b>{{ $data['title'] }}</b></div>'
                    );
                @elseif (isArchitect() == 1 || isElectrician() == 1)
                    $(this).before(
                        '<div><i id="list_data_loader" class="bx bx-loader bx-spin font-size-16 align-middle me-2" style=""></i><b>Lead & Deal</b></div>'
                    );
                @else
                    $(this).before(
                        '<div><i id="list_data_loader" class="bx bx-loader bx-spin font-size-16 align-middle me-2" style=""></i><b>{{ $data['title'] }} (<span class="text-primary" style="font-weight: bold;" id="list_record_count"></span>)</b></div>'
                    );
                @endif
            });

            $(document).ready(function() {
                $('#datatable_length').parent().removeClass().addClass(
                    'col-11 d-flex justify-content-between align-items-center card-header py-2 px-2');
                $('#datatable_length').parent().parent().addClass('justify-content-center');
                $('#datatable_length label').addClass('m-0');
                $('#datatable_filter').parent().removeClass().addClass('col-12');
                $('#datatable_filter label').addClass('input-group position-relative mb-0');
                $('#datatable_paginate').parent().removeClass().addClass('col-12 d-flex justify-content-center');
            });

            function FileStatusChange(id, is_active) {
                $.ajax({
                    type: 'POST',
                    url: ajaxURLFileStatusChange,
                    data: {
                        'id': id,
                        'is_active': is_active,
                        '_token': $("[name=_token]").val()
                    },
                    success: function(responseText) {
                        if (responseText['status'] == 1) {
                            toastr["success"](responseText['msg']);
                            $.ajax({
                                type: 'GET',
                                url: ajaxURLFileALL + "?lead_id=" + responseText['lead_id'] + "&islimit=1",
                                success: function(resultData) {
                                    if (resultData['status'] == 1) {
                                        $("#tab_files").html(resultData['view']);
                                        $('#file_loader').hide();
                                        if (resultData['files'] != '') {
                                            $.each(resultData['files'], function(index, value) {
                                                if (value['file_tag_id'] == 3) {
                                                    // $('#bill_attached_btn').show();
                                                    $('#reward_btn').show();
                                                    // $("#bill_attached_btn a").attr('href', value['download']);
                                                    tag_id = 3;
                                                    return false;
                                                } else {
                                                    // $('#bill_attached_btn').hide();
                                                    $('#reward_btn').hide();
                                                    // $("#bill_attached_btn a").attr('href', '');
                                                    tag_id = 0;
                                                }
                                            })

                                            if (resultData['active_bill_count'] == 0) {
                                                tag_id = 0;
                                            } else {
                                                tag_id = 3;
                                            }
                                        } else {
                                            tag_id = 0;
                                        }
                                        // getDataDetail(responseText['lead_id']);
                                    }
                                }
                            });
                        } else {
                            toastr["error"](responseText['msg']);
                        }
                    }
                })
            }

            $(document).ready(function() {
                adjustContainerHeight();
                $(window).on('resize', adjustContainerHeight);


            });

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


            function StatusApproved(id, lead_id, point) {


                $('#selectid').val(lead_id);
                $('#selectPoint').val(point);
                $('#selectFile_id').val(id);

                $('#modalStatusManu').modal('show');



                $.ajax({
                    type: 'GET',
                    data: {
                        "id": lead_id,
                    },
                    url: ajaxURLDatail,
                    success: function(responseText) {

                        if (responseText['status'] == 1) {
                            var fileData = responseText['data']['files'][0];
                            var id = $('#hod_query_file_id').val(fileData['id']);
                            $('#hod_query_file_status_tag_id').val(fileData['file_tag_id']);
                            $('#hod_query_file_amount_id').val(fileData['billing_amount']);
                            $('#hod_query_file_point_id').val(fileData['point']);

                        } else {
                            toastr["error"](responseText['msg']);
                        }
                    }
                });
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


                $('#hod_selectid').val(lead_id);
                $('#hod_selectPoint').val(point);
                $('#hod_selectFile_id').val(id);
                var file_id = $('#hod_selectFile_id').val();

                $.ajax({
                    type: 'GET',
                    data: {
                        "id": lead_id,
                    },
                    url: ajaxURLDatail,
                    success: function(responseText) {
                        if (responseText['status'] == 1) {
                            var fileData = responseText['data']['files'][0];
                            $('#hod_status_query_file_id').val(fileData['id']);
                        } else {
                            toastr["error"](responseText['msg']);
                        }
                    }
                });


                $('#modalHodStatus').modal('show');
                $('#is_hod_approved_status').trigger('change');
                statusQuestion();
            }

            function statusQuestion() {
                $.ajax({
                    type: 'GET',
                    data: {
                        "lead_id": lead_id,
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
            });

            function ChannelPartnerDetail(id) {
                $.ajax({
                    type: 'GET',
                    data: {
                        "id": id,
                    },
                    url: ajaxURChannelPartnerDetail,
                    success: function(responseText) {
                        if (responseText['status'] == 1) {
                            $('#channel_partner_first_name').text(responseText['data']['first_name'] + ' ' + responseText['data']['last_name']);
                            $('#channel_partner_first_name').attr('title', responseText['data']['first_name'] + ' ' + responseText['data']['last_name']);
                            $('#channel_partner_firm_name').text(responseText['data']['firm_name']);
                            $('#channel_partner_email').text(responseText['data']['email']);
                            $('#channel_partner_email').attr('title', responseText['data']['email']);
                            $('#channel_partner_number').text(responseText['data']['phone_number']);
                            $('#channel_partner_number').attr('title', responseText['data']['phone_number']);
                            $('#channel_partner_reference_type').text(responseText['data']['short_name']);
                            $('#channel_partner_address1').text(responseText['data']['address_line1']);
                            $('#channel_partner_address2').text(responseText['data']['address_line2']);
                            $('#sales_parson_name').text(responseText['data']['sales_person']);
                            $('#sales_parson_name').attr('title', responseText['data']['sales_person']);
                        } else {
                            toastr["error"](responseText['msg']);
                        }
                    }
                });

                $("#modalChannelPatDetail").modal('show');



            }

            function ArchitectDetail(id) {
                $.ajax({
                    type: 'GET',
                    data: {
                        "id": id,
                    },
                    url: ajaxURLArchitectDetail,
                    success: function(responseText) {
                        if (responseText['status'] == 1) {
                            $('#architect_first_name').text(responseText['data']['first_name'] + ' ' + responseText['data']['last_name']);
                            $('#architect_first_name').attr('title', responseText['data']['first_name'] + ' ' + responseText['data']['last_name']);
                            $('#architect_firm_name').text(responseText['data']['firm_name']);
                            $('#architect_email').text(responseText['data']['email']);
                            $('#architect_email').attr('title', responseText['data']['email']);
                            $('#architect_number').text(responseText['data']['phone_number']);
                            $('#architect_number').attr('title', responseText['data']['phone_number']);
                            $('#architect_reference_type').text(responseText['data']['short_name']);
                            $('#architect_address1').text(responseText['data']['address_line1']);
                            $('#architect_address2').text(responseText['data']['address_line2']);
                            $('#sales_parson_name_arc').text(responseText['data']['sales_person']);
                            $('#sales_parson_name_arc').attr('title', responseText['data']['sales_person']);
                        } else {
                            toastr["error"](responseText['msg']);
                        }
                    }
                });

                $("#modalArchitectDetail").modal('show');



            }
            
            function ElectricianDetail(id) {
                $.ajax({
                    type: 'GET',
                    data: {
                        "id": id,
                    },
                    url: ajaxURLElectricianDetail,
                    success: function(responseText) {
                        if (responseText['status'] == 1) {
                            $('#electrician_first_name').text(responseText['data']['first_name'] + ' ' + responseText['data']['last_name']);
                            $('#electrician_first_name').attr('title', responseText['data']['first_name'] + ' ' + responseText['data']['last_name']);
                            $('#electrician_email').text(responseText['data']['email']);
                            $('#electrician_email').attr('title', responseText['data']['email']);
                            $('#electrician_number').text(responseText['data']['phone_number']);
                            $('#electrician_number').attr('title', responseText['data']['phone_number']);
                            $('#electrician_reference_type').text(responseText['data']['short_name']);
                            $('#electrician_address1').text(responseText['data']['address_line1']);
                            $('#electrician_address2').text(responseText['data']['address_line2']);
                            $('#sales_parson_name_ele').text(responseText['data']['sales_person']);
                            $('#sales_parson_name_ele').attr('title', responseText['data']['sales_person']);
                        } else {
                            toastr["error"](responseText['msg']);
                        }
                    }
                });

                $("#modalElectricianDetail").modal('show');



            }
            
        </script>
        @include('crm.lead.action_script');
        @include('crm.lead.create_lead_script');
    @endsection
