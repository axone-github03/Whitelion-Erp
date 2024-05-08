@extends('layouts.main')
@section('title', $data['title'])
@section('content')

    <style type="text/css">

* {
        -webkit-text-size-adjust: none;
    }

    .switch {
        display: inline-block;
    }

    .switch input {
        display: none;
    }

    .switch small {
        display: inline-block;
        width: 92px;
        height: 25px;
        background: #ff6464;
        border-radius: 30px;
        position: relative;
        cursor: pointer;
    }

    .switch small:after {
        content: "Inactive";
        position: absolute;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        width: 95%;
        left: 0px;
        text-align: right;
        padding: 0 6px;
        box-sizing: border-box;
        line-height: 25px;
    }

    .switch small:before {
        content: "";
        position: absolute;
        width: 19px;
        height: 18px;
        background: #fff;
        border-radius: 50%;
        top: 3px;
        left: 5px;
        transition: .3s;
        box-shadow: -3px 0 3px rgba(0,0,0,0.1);
    }

    .switch input:checked~small {
        background: #34c38f;
        transition: .3s;
    }

    .switch input:checked~small:before {
        transform: translate(25px, 0px);
        transition: .3s;
        left: 44px !important;
    }

    .switch input:checked~small:after {
        content: "Active";
        text-align: center;
        left: -7px !important;
    }

    .add_new_note {
        background: #b5b5b521;
        border-radius: 5px;
    }

    .add_new_note::-webkit-input-placeholder {
        line-height: 25px;
        color: rgb(79, 79, 79) !important;
    }

    .table-striped>#leadQuotationTBody>tr:nth-of-type(odd)>* {
        --bs-table-accent-bg: white !important
    }

    .button {
        float: left;
        margin: 0 5px 0 0;
        width: 110px;
        height: 40px;
        position: relative;
    }

    .button span,
    .button input {
        display: block;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
    }

    .button input[type="checkbox"] {
        opacity: 0.011;
        z-index: 100;
        height: 35px;
    }

    .type input[type="checkbox"]:checked+span {
        background: #1f64bae3;
        color: white;
        border-radius: 4px;
    }

    .titlecheckbox input[type="checkbox"]:checked+label {
        color: #3673c0;
    }

    .button span {
        cursor: pointer;
        z-index: 90;
        color: #878787;
        font-weight: 700;
        line-height: 1.5em;
        background-color: #fff;
    }

    .c-white {
        color: white !important;
    }

    .appendixmark {
        font-size: 8pt;
        border: 1px solid #bb6161;
        border-radius: 15px;
        background-color: #bb6161;
        position: relative;
        top: 0px;
        padding: 0px 4px 0px 4px;
        color: white;
    }

    .checkbox {
        margin-right: 10px;
    }

    .label-text {
        font-size: 1rem;
    }

    .input-checkbox {
        width: 10px;
    }

    .vh {
        position: absolute !important;
        clip: rect(1px, 1px, 1px, 1px);
        padding: 0 !important;
        border: 0 !important;
        height: 1px !important;
        width: 1px !important;
        overflow: hidden;
    }

    input[type="checkbox"]:checked~label:before {
        vertical-align: middle;
        background: #3673c0 no-repeat center;
        background-size: 9px 9px;
        background-image: url(data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTYuMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgd2lkdGg9IjE2cHgiIGhlaWdodD0iMTZweCIgdmlld0JveD0iMCAwIDQ1LjcwMSA0NS43IiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA0NS43MDEgNDUuNzsiIHhtbDpzcGFjZT0icHJlc2VydmUiPgo8Zz4KCTxnPgoJCTxwYXRoIGQ9Ik0yMC42ODcsMzguMzMyYy0yLjA3MiwyLjA3Mi01LjQzNCwyLjA3Mi03LjUwNSwwTDEuNTU0LDI2LjcwNGMtMi4wNzItMi4wNzEtMi4wNzItNS40MzMsMC03LjUwNCAgICBjMi4wNzEtMi4wNzIsNS40MzMtMi4wNzIsNy41MDUsMGw2LjkyOCw2LjkyN2MwLjUyMywwLjUyMiwxLjM3MiwwLjUyMiwxLjg5NiwwTDM2LjY0Miw3LjM2OGMyLjA3MS0yLjA3Miw1LjQzMy0yLjA3Miw3LjUwNSwwICAgIGMwLjk5NSwwLjk5NSwxLjU1NCwyLjM0NSwxLjU1NCwzLjc1MmMwLDEuNDA3LTAuNTU5LDIuNzU3LTEuNTU0LDMuNzUyTDIwLjY4NywzOC4zMzJ6IiBmaWxsPSIjRkZGRkZGIi8+Cgk8L2c+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPC9zdmc+Cg==);
    }

    .label-text::before {
        content: '';
        width: 15px;
        height: 15px;
        background: #f2f2f2;
        border: 1px solid rgba(75, 101, 132, 0.3);
        display: inline-block;
        margin-right: 10px;
    }

    .error-border {
        border: 1px solid #ffb1b1 !important;
    }
        .page-content {
            padding: calc(70px + 24px) calc(24px / 2) 0px calc(24px / 2) !important;
        }

        td p {
            max-width: 100%;
            white-space: break-spaces;
            word-break: break-all;
        }

        td {
            vertical-align: middle;
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

        .b-bottom {
            border-bottom: 2px solid #f1e9e9;
        }

        @media (max-width: 1440px) {
            .page-content {
                font-size: xx-small !important;
            }

            .funnel1 {
                font-size: xx-small !important;
            }
        }

        .funnel1 {
            height: 30px;
            width: auto;
            float: left;
            margin-right: 0.50%;
            position: relative;
            text-align: center;
            text-indent: 16px;
            line-height: 30px;
            font-size: 14px;
            background: #A9A9A9;
            color: #ffffff;
            /* box-shadow: inset 0px 20px 20px 20px rgb(0 0 0 / 15%); */
        }

        .funnel1.active {
            background: #556ee6;
            color: #fff;
        }

        .funnel1.active:before {
            border-left-color: #556ee6 !important;
            z-index: 999 !important;
        }

        .funnel1.active:before,
        .funnel1.active:after {
            position: absolute !important;
            content: '' !important;
            z-index: 1;
            width: 0px !important;
            height: 0 !important;
            top: 50% !important;
            margin: -15px 0 0 !important;
            border: 15px solid transparent;
            border-left-color: #fff;
        }

        .funnel1:before,
        .funnel1:after {
            position: absolute;
            content: '';
            z-index: 1;
            width: 0px;
            height: 0;
            top: 50%;
            margin: -15px 0 0;
            border: 15px solid transparent;
            border-left-color: #f8f8fb;
        }

        .funnel1:after {
            left: 0%;
        }

        .funnel1:before {
            left: 100%;
            z-index: 99;
        }

        .funnel1:before {
            border-left-color: #A9A9A9;
        }

        .bg-primary:before {
            border-left-color: #556ee6;
        }

        .funnel1:hover {
            color: white !important;
        }


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

        .switch {
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

        input:checked+.slider {
            background-color: #07cd1266;
        }

        .slider.round {
            border-radius: 34px;
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

        input:checked+.slider:before {
            -webkit-transform: translateX(17px);
            -ms-transform: translateX(17px);
            transform: translateX(17px);
        }

        .slider.round:before {
            border-radius: 50%;
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
    </style>
    <link href="{{ asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet"
        type="text/css">
    <div class="page-content">
        <div class="container-fluid" id="custom_height">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18"> {{ $data['title'] }}</h4>
                        <div class="page-title-right"></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 pe-0">
                    <div class="userscomman row text-start align-items-center ps-3 mt-2 mb-3">
                        <a href="#" class="funnel1 lead_status_filter_remove" id="past_due_quotation_action"
                            value="5">Quotation(<span class="">0</span>)</a>
                        <a href="#" class="funnel1 lead_status_filter_remove"
                            id="past_due_task_all_open_action">Task(<span class="">0</span>)</a>
                        <a href="#" class="funnel1 lead_status_filter_remove"
                            id="past_due_call_all_open_action">Call(<span class="">0</span>)</a>
                        <a href="#" class="funnel1 lead_status_filter_remove"
                            id="past_due_metting_all_open_action">Metting(<span class="">0</span>)</a>
                        <a href="#" class="funnel1 lead_status_filter_remove bg-primary" id="past_due_all_open_action"
                            value="4">All(<span class="">0</span>)</a>
                    </div>
                    <div>
                        <div class="card lead-detail">
                            <div class="card-header bg-transparent border-bottom p-2 d-flex align-items-center justify-content-between">
                                <b>Past Due Open Action</b>
                                <div class="d-flex justify-content-end align-items-center">
                                    <div class="me-2 input-group" id="past_action_from_date_picker">
                                        <input style="height: 30px;" autocomplete="off" type="text" class="form-control"
                                            placeholder="From date" data-date-format="yyyy-mm-dd"
                                            data-date-container='#past_action_from_date_picker' data-provide="datepicker"
                                            data-date-autoclose="true" required name="past_action_from_date_id"
                                            value="" id="past_action_from_date_id">
                                    </div>
                                    <div class="me-2 input-group" id="past_action_to_date">
                                        <input style="height: 30px;" autocomplete="off" type="text" class="form-control"
                                            placeholder="To date" data-date-format="yyyy-mm-dd"
                                            data-date-container='#past_action_to_date' data-provide="datepicker"
                                            data-date-autoclose="true" required name="past_action_to_date_id"
                                            value="" id="past_action_to_date_id">
                                    </div>
                                    <div class=" ps-2" id="past_action_from_date">
                                        <button class="btn btn-primary" onclick="PreviousCallAjax(1)">search</button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body border-bottom p-2 overflow-auto"id="past_due_option_action_height">
                                <div id="previous_open_call_task">

                                </div>
                                <div id="previous_open_meeting_task">

                                </div>
                                <div id="previous_open_task">

                                </div>
                                <div class="d-none" id="previous_quotation_action">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 ps-0">
                    <div class="userscomman row text-start align-items-center ps-3 mt-2 mb-3">
                        <a href="#" class="funnel1 lead_status_filter_remove" id="today_task_all_open_action"
                            value="1">Task(<span class="">0</span>)</a>
                        <a href="#" class="funnel1 lead_status_filter_remove" id="today_call_all_open_action"
                            value="2">Call(<span class="">0</span>)</a>
                        <a href="#" class="funnel1 lead_status_filter_remove" id="today_metting_all_open_action"
                            value="3">Metting(<span class="">0</span>)</a>
                        <a href="#" class="funnel1 lead_status_filter_remove bg-primary" id="today_all_open_action"
                            value="4">All(<span class="">0</span>)</a>
                    </div>
                    <div>
                        <div class="card lead-detail mb-0">
                            <div class="card-header bg-transparent border-bottom p-2">
                                <b>Today's Open Action</b>
                            </div>
                            <div class="card-body border-bottom p-2 overflow-auto" id="today_open_action_height">
                                <div id="today_open_call_task">

                                </div>
                                <div id="today_open_meeting_task">

                                </div>
                                <div id="today_open_task">

                                </div>
                            </div>
                        </div>

                        <div class="card lead-detail">
                            <div
                                class="card-header bg-transparent border-bottom p-2 d-flex align-items-center justify-content-between">
                                <div><b>Today's Close Action</b></div>
                                <div class="d-flex justify-content-end align-items-center">
                                    <div class="me-2 input-group" id="close_action_from_date_picker">
                                        <input style="height: 30px;" autocomplete="off" type="text" class="form-control"
                                            placeholder="From date" data-date-format="yyyy-mm-dd"
                                            data-date-container='#close_action_from_date_picker' data-provide="datepicker"
                                            data-date-autoclose="true" required name="close_action_from_date_id"
                                            value="" id="close_action_from_date_id">
                                    </div>
                                    <div class="me-2 input-group" id="close_action_to_date">
                                        <input style="height: 30px;" autocomplete="off" type="text" class="form-control"
                                            placeholder="To date" data-date-format="yyyy-mm-dd"
                                            data-date-container='#close_action_to_date' data-provide="datepicker"
                                            data-date-autoclose="true" required name="close_action_to_date_id"
                                            value="" id="close_action_to_date_id">
                                    </div>
                                    <div class=" ps-2" id="close_action_from_date">
                                        <button class="btn btn-primary" onclick="TodayCloseCallAjax(1)">search</button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body border-bottom p-2 overflow-auto" id="today_close_action_height">
                                <div id="today_close_call_task">

                                </div>
                                <div id="today_close_meeting_task">

                                </div>
                                <div id="today_close_task">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="filtermodal" tabindex="-1" role="dialog"
                aria-labelledby="exampleModalCenterTitle" aria-hidden="true" style="background-color: #0000003b;">
                <div class="modal-dialog modal-dialog-centered justify-content-center" role="document">
                    <div class="modal-content" style="background-color: #efefef;width: auto !important;">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLongTitle">Quotation Pdf Filter</h5>
                            <button type="button" class="close btn-close" data-dismiss="modal" aria-label="Close">
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="d-flex flex-column mb-2 align-items-center">
                                <div class="titlecheckbox">
                                    <input type="checkbox" name="areatitle" id="areatitle"
                                        class="vh areasummarytitle"><label class="label-text"
                                        for="areatitle">Summary</label>
                                </div>
                                <div class="areasummary" style="display: none">
                                    <div class="button type">
                                        <input type="checkbox" id="area">
                                        <span class="btn btn-default" for="a5">Roomwise</span>
                                    </div>
                                    <div class="button type">
                                        <input type="checkbox" id="product">
                                        <span class="btn btn-default" for="a5">Count</span>
                                    </div>
                                </div>
                            </div>

                            <div style="border-bottom: 2px solid #ababab"></div>

                            <div class="d-flex flex-column mt-2 mb-2 align-items-center">
                                <div class="titlecheckbox">
                                    <input type="checkbox" name="areadetailtitle" id="areadetailtitle"
                                        class="vh areadetailtitle"><label class="label-text"
                                        for="areadetailtitle">Roomwise</label>
                                </div>
                                <div class="areadetailsummary" style="display: none">
                                    <div class="button type">
                                        <input type="checkbox" id="areagst" class="allgst" />
                                        <span class="btn btn-default" for="a5">GST</span>
                                    </div>
                                    <div class="button type">
                                        <input type="checkbox" id="areadiscount" class="alldiscount" />
                                        <span class="btn btn-default" for="a5">Discount</span>
                                    </div>
                                    <div class="button type">
                                        <input type="checkbox" id="arearate" class="allnetamount" />
                                        <span class="btn btn-default" for="a5">Net</span>
                                    </div>
                                </div>
                            </div>

                            <div style="border-bottom: 2px solid #ababab"></div>

                            <div class="d-flex flex-column  mt-2 mb-2 align-items-center">
                                <div class="titlecheckbox">
                                    <input type="checkbox" name="producttitle" id="producttitle"
                                        class="vh producttitle"><label class="label-text"
                                        for="producttitle">Boardwise</label>
                                </div>
                                <div class="productdetailsummary" style="display: none">
                                    <div class="button type">
                                        <input type="checkbox" id="productgst" class="allgst" />
                                        <span class="btn btn-default" for="a5">GST</span>
                                    </div>
                                    <div class="button type">
                                        <input type="checkbox" id="productdiscount" class="alldiscount" />
                                        <span class="btn btn-default" for="a5">Discount</span>
                                    </div>
                                    <div class="button type">
                                        <input type="checkbox" id="productrate" class="allnetamount" />
                                        <span class="btn btn-default" for="a5">Net</span>
                                    </div>
                                </div>
                            </div>

                            <div style="border-bottom: 2px solid #ababab"></div>

                            <div class="d-flex flex-column mt-2 align-items-center">
                                <div class="titlecheckbox">
                                    <input type="checkbox" name="whiteliontitle" id="whiteliontitle"
                                        class="vh whiteliontitle"><label class="label-text"
                                        for="whiteliontitle">Whitelion
                                        And Other</label>
                                </div>
                                <div class="wltandotherdetailsummary" style="display: none">
                                    <div class="button type">
                                        <input type="checkbox" id="wltandotherproductgst" class="allgst" />
                                        <span class="btn btn-default" for="a5">GST</span>
                                    </div>
                                    <div class="button type">
                                        <input type="checkbox" id="wltandotherdiscount" class="alldiscount" />
                                        <span class="btn btn-default" for="a5">Discount</span>
                                    </div>
                                    <div class="button type">
                                        <input type="checkbox" id="wltandothernet" class="allnetamount" />
                                        <span class="btn btn-default" for="a5">Net</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary close" data-dismiss="modal">Close</button>
                            <a href="" class="btn btn-primary" id="itempdfdownload" target="_blank">Download</a>
                            {{-- <button type="button" class="btn btn-primary" id="pdfpreviewshow">View</button> --}}
                        </div>
                        <input type="hidden" name="" id="Quot_id">
                        <input type="hidden" name="" id="Quotgroup_id">
                    </div>
                </div>
            </div>

            @include('crm.lead.action_modal');
        </div>
    </div>

    @csrf
@endsection('content')
@section('custom-scripts')

    <script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
    <script type="text/javascript">
        var ajaxCall = "{{ route('crm.lead.myaction.call.ajax') }}";
        var ajaxPreviousCall = "{{ route('crm.lead.myaction.call.previous.ajax') }}";
        var ajaxTodayCloseCall = "{{ route('crm.lead.myaction.today.close.call.ajax') }}";
        var PrintDownloadUrl = "{{ route('quot.master.itemwiseprint.download') }}";

        function getLang() {
            if (navigator.languages != undefined)
                return navigator.languages[0];
            return navigator.language;
        }

        $(function() {
            $('.datetimepicker').datetimepicker({
                format: 'dd:mm:yyyy HH:ss a'
            });
        });

        const currentDate = new Date().toLocaleString(getLang(), {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',

        }).replaceAll('/', '-').replaceAll(':', '').replaceAll(' ', 'T');


        var csrfToken = $("[name=_token").val();

        function TodayCallAjax() {
            $.ajax({
                url: ajaxCall,
                type: "post",
                data: {
                    "_token": csrfToken,
                },
                success: function(result) {
                    $('#today_open_call_task').html(result['call_data']);
                    $('#today_open_meeting_task').html(result['meeeting_data']);
                    $('#today_open_task').html(result['task_data']);

                    $('#today_call_all_open_action').html('Call(<span class="">' + result['recordsCallTotal'] +
                        '</span>)');
                    $('#today_metting_all_open_action').html('Meeting(<span class="">' + result[
                        'recordsMeetingTotal'] + '</span>)');
                    $('#today_task_all_open_action').html('Task(<span class="">' + result['recordsTaskTotal'] +
                        '</span>)');
                    $('#today_all_open_action').html('All(<span class="">' + result['allrecordsTotal'] +
                        '</span>)');
                }
            })
        }

        function PreviousCallAjax(is_filter) {
            if (is_filter == 1) {
                from_date = $('#past_action_from_date_id').val();
                to_date = $('#past_action_to_date_id').val();
            } else {
                from_date = 0
                to_date = 0;
            }
            $.ajax({
                url: ajaxPreviousCall,
                type: "post",
                data: {
                    "_token": csrfToken,
                    "from_date": from_date,
                    "to_date": to_date,
                },
                success: function(result) {
                    $('#previous_open_call_task').html(result['call_data']);
                    $('#previous_open_meeting_task').html(result['meeeting_data']);
                    $('#previous_open_task').html(result['task_data']);
                    $('#previous_quotation_action').html(result['quotation_request']);

                    $('#past_due_call_all_open_action').html('Call(<span class="">' + result[
                        'recordsCallTotal'] + '</span>)')
                    $('#past_due_metting_all_open_action').html('Meeting(<span class="">' + result[
                        'recordsMeetingTotal'] + '</span>)')
                    $('#past_due_task_all_open_action').html('Task(<span class="">' + result[
                        'recordsTaskTotal'] + '</span>)')
                    $('#past_due_all_open_action').html('All(<span class="">' + result['allrecordsTotal'] +
                        '</span>)')
                    $('#past_due_quotation_action').html('Quotation(<span class="">' + result[
                        'recordsQuotationTotal'] + '</span>)')
                }
            })
        }

        function TodayCloseCallAjax(is_filter) {
            if (is_filter == 1) {
                from_date = $('#close_action_from_date_id').val();
                to_date = $('#close_action_to_date_id').val();
            } else {
                from_date = 0
                to_date = 0;
            }
            $.ajax({
                url: ajaxTodayCloseCall,
                type: "post",
                data: {
                    "_token": csrfToken,
                    "from_date": from_date,
                    "to_date": to_date,
                },
                success: function(result) {
                    $('#today_close_call_task').html(result['call_data']);
                    $('#today_close_meeting_task').html(result['meeeting_data']);
                    $('#today_close_task').html(result['task_data']);
                }
            })
        }

        $('#today_task_all_open_action').on('click', function() {
            $('#today_task_all_open_action').addClass('bg-primary');
            $('#today_call_all_open_action').removeClass('bg-primary');
            $('#today_metting_all_open_action').removeClass('bg-primary');
            $('#today_all_open_action').removeClass('bg-primary');

            $('#today_open_meeting_task').addClass('d-none');
            $('#today_open_call_task').addClass('d-none');
            $('#today_open_task').addClass('d-block');
            $('#today_open_task').removeClass('d-none');
        })

        $('#today_call_all_open_action').on('click', function() {
            $('#today_task_all_open_action').removeClass('bg-primary');
            $('#today_call_all_open_action').addClass('bg-primary');
            $('#today_metting_all_open_action').removeClass('bg-primary');
            $('#today_all_open_action').removeClass('bg-primary');

            $('#today_open_meeting_task').addClass('d-none');
            $('#today_open_call_task').addClass('d-block');
            $('#today_open_call_task').removeClass('d-none');
            $('#today_open_task').addClass('d-none');
        })

        $('#today_metting_all_open_action').on('click', function() {
            $('#today_task_all_open_action').removeClass('bg-primary');
            $('#today_call_all_open_action').removeClass('bg-primary');
            $('#today_metting_all_open_action').addClass('bg-primary');
            $('#today_all_open_action').removeClass('bg-primary');

            $('#today_open_meeting_task').addClass('d-block');
            $('#today_open_meeting_task').removeClass('d-none');
            $('#today_open_call_task').addClass('d-none');
            $('#today_open_task').addClass('d-none');
        })


        $('#past_due_task_all_open_action').on('click', function() {
            $('#past_due_task_all_open_action').addClass('bg-primary');
            $('#past_due_call_all_open_action').removeClass('bg-primary');
            $('#past_due_metting_all_open_action').removeClass('bg-primary');
            $('#past_due_all_open_action').removeClass('bg-primary');
            $('#past_due_quotation_action').removeClass('bg-primary');

            $('#previous_open_meeting_task').addClass('d-none');
            $('#previous_open_call_task').addClass('d-none');
            $('#previous_open_task').addClass('d-block');
            $('#previous_open_task').removeClass('d-none');
            $('#previous_quotation_action').addClass('d-none');
        })

        $('#past_due_call_all_open_action').on('click', function() {
            $('#past_due_task_all_open_action').removeClass('bg-primary');
            $('#past_due_call_all_open_action').addClass('bg-primary');
            $('#past_due_metting_all_open_action').removeClass('bg-primary');
            $('#past_due_all_open_action').removeClass('bg-primary');
            $('#past_due_quotation_action').removeClass('bg-primary');

            $('#previous_open_meeting_task').addClass('d-none');
            $('#previous_open_call_task').addClass('d-block');
            $('#previous_open_call_task').removeClass('d-none');
            $('#previous_open_task').addClass('d-none');
            $('#previous_quotation_action').addClass('d-none');
        })

        $('#past_due_metting_all_open_action').on('click', function() {
            $('#past_due_task_all_open_action').removeClass('bg-primary');
            $('#past_due_call_all_open_action').removeClass('bg-primary');
            $('#past_due_metting_all_open_action').addClass('bg-primary');
            $('#past_due_all_open_action').removeClass('bg-primary');
            $('#past_due_quotation_action').removeClass('bg-primary');

            $('#previous_open_meeting_task').addClass('d-block');
            $('#previous_open_meeting_task').removeClass('d-none');
            $('#previous_open_call_task').addClass('d-none');
            $('#previous_open_task').addClass('d-none');
            $('#previous_quotation_action').addClass('d-none');
        })


        $('#past_due_all_open_action').on('click', function() {
            $('#past_due_task_all_open_action').removeClass('bg-primary');
            $('#past_due_call_all_open_action').removeClass('bg-primary');
            $('#past_due_metting_all_open_action').removeClass('bg-primary');
            $('#past_due_all_open_action').addClass('bg-primary');
            $('#past_due_quotation_action').removeClass('bg-primary');

            $('#previous_open_meeting_task').removeClass('d-none');
            $('#previous_open_call_task').removeClass('d-none');
            $('#previous_open_task').removeClass('d-none');
            $('#previous_quotation_action').addClass('d-none');
        })

        $('#today_all_open_action').on('click', function() {
            $('#today_task_all_open_action').removeClass('bg-primary');
            $('#today_call_all_open_action').removeClass('bg-primary');
            $('#today_metting_all_open_action').removeClass('bg-primary');
            $('#today_all_open_action').addClass('bg-primary');
            $('#past_due_quotation_action').removeClass('bg-primary');

            $('#today_open_meeting_task').removeClass('d-none');
            $('#today_open_call_task').removeClass('d-none');
            $('#today_open_task').removeClass('d-none');
        })

        $('#past_due_quotation_action').on('click', function() {
            $('#past_due_task_all_open_action').removeClass('bg-primary');
            $('#past_due_call_all_open_action').removeClass('bg-primary');
            $('#past_due_metting_all_open_action').removeClass('bg-primary');
            $('#past_due_all_open_action').removeClass('bg-primary');
            $('#past_due_quotation_action').addClass('bg-primary');

            $('#previous_open_call_task').addClass('d-none');
            $('#previous_open_meeting_task').addClass('d-none');
            $('#previous_open_task').addClass('d-none');
            $('#previous_quotation_action').removeClass('d-none');
        })


        $(document).ready(function() {
            PreviousCallAjax();
            TodayCallAjax();
            TodayCloseCallAjax(0);

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
            $("#formLeadCall").ajaxForm(options);
            $("#formLeadTask").ajaxForm(options);
            $("#formLeadMeeting").ajaxForm(options);
            $("#formAutogenerateAction").ajaxForm(options);
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
            return true;
        }

        // post-submit callback
        function showResponse(responseText, statusText, xhr, $form) {
            if ($form[0]['id'] == "formLeadCall") {

                if (responseText['status'] == 1) {
                    PreviousCallAjax();
                    TodayCallAjax();
                    toastr["success"](responseText['msg']);
                    $('#formLeadCall').trigger("reset");
                    $("#modalCall").modal('hide');
                    $("#lead_call_move_to_close").val(0);
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
            } else if ($form[0]['id'] == "formLeadTask") {
                PreviousCallAjax();
                TodayCallAjax();
                if (responseText['status'] == 1) {
                    toastr["success"](responseText['msg']);
                    $('#formTaskCall').trigger("reset");
                    $("#modalTask").modal('hide');
                    $("#lead_task_move_to_close").val(0);
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

            } else if ($form[0]['id'] == "formLeadMeeting") {
                PreviousCallAjax();
                TodayCallAjax();
                if (responseText['status'] == 1) {
                    toastr["success"](responseText['msg']);
                    $('#formLeadMeeting').trigger("reset");
                    $("#modalMeeting").modal('hide');
                    $('#lead_meeting_move_to_close_btn').val(0)
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
                PreviousCallAjax();
                TodayCallAjax();
                if (responseText['status'] == 1) {
                    toastr["success"](responseText['msg']);
                    $('#formAutogenerateAction').trigger("reset");
                    $("#modalAutoScheduleCall").modal('hide');
                } else if (responseText['status'] == 0) {
                    toastr["error"](responseText['msg']);
                }
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

        // function searchFilter() {
        //     $('#today_close_call_task').html('');
        //     $('#today_close_meeting_task').html("");
        //     $('#today_close_task').html("");

        //     $.ajax({
        //         url: ajaxSearchCloseAction,
        //         type: "post",
        //         data: {
        //             "_token": csrfToken,
        //             "from_date": $('#close_action_from_date_id').val(),
        //             "to_date": $('#close_action_to_date_id').val(),
        //         },
        //         success: function(result) {
        //             $('#today_close_call_task').html(result['call_data']);
        //             $('#today_close_meeting_task').html(result['meeeting_data']);
        //             $('#today_close_task').html(result['task_data']);
        //         }
        //     })
        // }

        $(document).ready(function() {
            adjustContainerHeight();
            $(window).on('resize', adjustContainerHeight);
        });

        function adjustContainerHeight() {
            var windowHeight = $(window).height() - 120;
            var windowWidth = $(window).width();
            if (windowWidth <= 1440) {
                $('body').addClass('vertical-collpsed');
            }
            max_height = windowHeight - 180;
            $('#past_due_option_action_height').css('max-height', max_height + 'px');
            $('#today_open_action_height').css('max-height', max_height / 2 - 20 + 'px');
            $('#today_close_action_height').css('max-height', max_height / 2 - 20 + 'px');
            $('#custom_height').css('height', windowHeight + 'px');
        }
    </script>
    @include('crm.lead.action_script');
@endsection
