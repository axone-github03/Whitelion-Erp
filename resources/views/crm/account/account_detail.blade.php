@extends('layouts.main')
@section('title', $data['title'])
@section('content')

    <style>
        * {
            -webkit-text-size-adjust: none;
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
    </style>
    <style>
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

        .lead-detail .form-control,
        .input-group-text {
            /* padding: 2px 11px; */
            border: none;
        }

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

        .chat-list li a {
            padding: 8px 13px;
            border-radius: 0px;
        }

        .chat-list li.active a {
            background-color: rgb(141, 226, 255);
            border-color: transparent;
            -webkit-box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, .03);
            box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, .03);
        }

        .chat-list li a:hover {
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
            box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
        }

        .status-active-class {
            background-color: #11dc11;
            color: #ffffff !important
        }

        .next-status-active-class {
            background-color: #556ee6;
            color: #ffffff !important
        }

        .border-bottom {
            border-bottom: 1px solid #d1d1d1 !important;
        }

        .border-none {
            border: none !important;
        }

        #modalCall .select2-selection.select2-selection--single {
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
        }

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

        #datatable_wrapper:nth-child(3) {
            position: fixed;
        }

        .active_lead {
            background-color: rgb(141, 226, 255);
            border-color: transparent;
            -webkit-box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, .03);
            box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, .03);
        }
    </style>

    <div class="page-content">
        <div class="container-fluid">
            <div class="row ms-1">
                <div class="d-lg-flex" style="height: 700px;">
                    <div class="chat-leftsidebar me-lg-4">
                        <div class="tab-content py-4">
                            <input type="hidden" name="" value="0" id="hidden_status">
                            <input type="hidden" name="" value="0" id="hidden_is_advancefilter">
                            <div class="tab-pane show active lead-list" id="chat">
                                <table id="account_datatable" class="table static_hover dt-responsive  nowrap w-100"
                                    style="max-height: 600px;">
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
                    <div class="w-100 user-chat py-4">
                        <div class="">
                            <div class="" id="user_detail" style="height: 700px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- start page title -->
        {{-- <div class="chat-leftsidebar me-lg-4">
            <div class="tab-content py-4">
                <div class="card-header">
                    <b>{{ $data['title'] }}</b>
                </div>
                <div class="app-search lead-search-form d-none d-lg-block">
                    <div class="position-relative">
                        <input type="search" class="form-control" id="input_search" name="input_search" placeholder="Search...">
                        <span class="bx bx-search-alt"></span>
                    </div>      
                </div>
                <div class="tab-pane show active lead-list" id="chat">
                    <div>
                        <ul id="sideBarUI" class="list-unstyled chat-list" data-simplebar style="background: rgb(255 255 255);max-height: 650px;">
                            
                        </ul>
                    </div>
                </div>
            </div>
        </div> --}}

        {{-- <div class="row">
            <div class="col-12" id="top-menu-lead">
                <div class="d-sm-flex align-items-center justify-content-between">
                    <div class="userscomman">
                        <form class="app-search lead-search-form d-none d-lg-block">
                            <div class="position-relative">
                                <input type="text" class="form-control" placeholder="Search...">
                                <span class="bx bx-search-alt"></span>
                            </div>
                        </form>
                        <button type="button" class="btn btn-outline-secondary waves-effect"><i class="bx bx-filter label-icon "></i> Filter</button>
                    </div>
                </div>
            </div>
            <!-- end page title -->
            <div class="row">
                <div class="d-lg-flex">
                    <div class="chat-leftsidebar me-lg-4">
                        <div class="tab-content py-4">
                            <div class="tab-pane show active lead-list" id="chat">
                                <div>
                                    <div class="card-header bg-transparent border-bottom">
                                        <b>{{ $data['title']}}</b>




                                    </div>
                                    <ul id="sideBarUI" class="list-unstyled chat-list lead-custom-scroll-1" data-simplebar style="background: rgb(141 226 255)">




                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="w-100 user-chat py-4">
                            <div class="" id="user_detail">
                            </div>
                        </div>
                    </div>
                </div>

            </div>





        </div> --}}
        <!-- container-fluid -->
    </div>
    @include('crm.lead.action_modal')
    <div class="modal fade" id="modalLeadContact" data-bs-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="modalLeadContactLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLeadContactLabel"> Lead Contact</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formLeadContact" action="{{ route('crm.account.contact.save') }}" method="POST"
                    class="needs-validation" novalidate>
                    <div class="modal-body">

                        @csrf



                        <div class="col-md-12 text-center loadingcls">


                            <button type="button" class="btn btn-light waves-effect">
                                <i class="bx bx-hourglass bx-spin font-size-16 align-middle me-2"></i> Loading
                            </button>


                        </div>

                        <input type="hidden" name="account_contact_user_id" id="account_contact_user_id">
                        <input type="hidden" name="account_contact_id" id="account_contact_id">

                        <div class="row">
                            <div class="col-md-6">

                                <div class="row mb-1">
                                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">First
                                        name</label>
                                    <div class="col-sm-9">
                                        <input class="form-control" id="account_contact_first_name"
                                            name="account_contact_first_name" placeholder="First Name" value=""
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
                                            <input type="number" class="form-control" id="account_contact_phone_number"
                                                name="account_contact_phone_number" placeholder="Phone number"
                                                value="" required>

                                        </div>
                                    </div>


                                </div>

                                <div class="row mb-1">
                                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Email</label>
                                    <div class="col-sm-9">
                                        <input class="form-control" id="account_contact_email" name="account_contact_email"
                                            placeholder="Email" value="" required>
                                    </div>
                                </div>


                            </div>

                            <div class="col-md-6">

                                <div class="row mb-1">
                                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Last
                                        name</label>
                                    <div class="col-sm-9">
                                        <input class="form-control" id="account_contact_last_name"
                                            name="account_contact_last_name" placeholder="Last Name" value=""
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
                                                id="account_contact_alernate_phone_number"
                                                name="account_contact_alernate_phone_number" placeholder="Phone number"
                                                value="">

                                        </div>
                                    </div>


                                </div>


                                <div class="row mb-1">
                                    <label for="horizontal-firstname-input" class="col-sm-3 col-form-label">Tag</label>
                                    <div class="col-sm-9">
                                        <select class="form-control select2-ajax" id="account_contact_tag_id"
                                            name="account_contact_tag_id" required>

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

@endsection('content')

@section('custom-scripts')
    <script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('assets/js/pages/jquery.form.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

    <script type="text/javascript">
        var ajaxURLDataList = "{{ route('crm.account.list') }}";
        var ajaxURLDataDatail = "{{ route('crm.lead.account.detail.view') }}";
        var ajaxURLSearchContactTag = "{{ route('crm.lead.search.contact.tag') }}";
        var ajaxURContactDetail = "{{ route('crm.account.contact.detail') }}";
        var ajaxURLDataListAjax = "{{ route('crm.lead.account.list.ajax') }}";
        var ajaxURLSearchUserTag = "{{ route('search.user.tag') }}";
        var ajaxURLUpdateUserDetail = "{{ route('save.user.detail') }}";

        var csrfToken = $("[name=_token").val();
        var viewLeadId = "{{ $data['id'] }}";
        var viewUserId = "{{ $data['id'] }}";

        $("#account_contact_tag_id").select2({
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




        // A $( document ).ready() block.
        $(document).ready(function() {
            var newHeight1 = $(window).height() - $('#top-menu-lead').outerHeight() - 150;
            var newHeight2 = $(window).height() - $('#top-menu-lead').outerHeight() - 150 - 100;

            $('.lead-custom-scroll-1').css("max-height", newHeight1);
            $('.lead-custom-scroll-2').css("max-height", newHeight2);
            var isdetailload = 0;
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
            $("#formLeadStatusChange").ajaxForm(options);










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
                    reloadList();
                    loadDetail = 0;
                    getDataDetail(responseText['id']);



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

            } else if ($form[0]['id'] == "formLeadContact") {

                if (responseText['status'] == 1) {
                    toastr["success"](responseText['msg']);
                    $('#formLeadContact').trigger("reset");
                    $("#modalLeadContact").modal('hide');
                    getDataDetail(responseText['id']);


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

        var lastPageUserId = 0;
        var loadDetail = 1;

        function getDataList(searchValue = "") {

            isdetailload = 0;
            $.ajax({
                type: 'GET',
                url: ajaxURLDataList + "?id=" + lastPageUserId,
                data: {
                    'search': searchValue,
                },
                success: function(resultData) {
                    if (resultData['status'] == 1) {

                        $("#sideBarUI .simplebar-content").html("");
                        if (lastPageUserId == 0) {
                            $("#sideBarUI .simplebar-content").html("");
                            if (loadDetail == 1) {
                                if (resultData['FirstPageLeadId'] != 0) {
                                    getDataDetail(viewUserId);
                                }
                                isdetailload = 1;
                            } else {
                                loadDetail = 1;
                            }
                        }

                        $("#sideBarUI .simplebar-content").append(resultData['view']);
                        lastPageUserId = resultData['lastPageUserId'];
                    }
                }
            });

        }


        function reloadList() {

            lastPageUserId = 0;
            reloadAccList()
            // getDataList();

        }
        // getDataList();


        function getDataDetail(id) {

            $("#lead_" + id).parent().parent().addClass('active_lead');
            // $("#lead_" + id).parent().parent().addClass('active_lead');
            var scrollTopHeightDataTable = $('#user_detail .lead-custom-scroll-2').prop('scrollTop');

            $.ajax({
                type: 'GET',
                url: ajaxURLDataDatail + "?id=" + id,
                success: function(resultData) {
                    if (resultData['status'] == 1) {
                        $("#user_detail").html(resultData['view']);
                        $(".user_li.active").removeClass('active');
                        $("#user_" + id).addClass('active');

                        $(".lead_li").parent().parent().removeClass('active_lead');
                        $("#lead_" + id).parent().parent().addClass('active_lead');

                        $("#user_detail .lead-custom-scroll-2").animate({
                            scrollTop: scrollTopHeightDataTable
                        }, 10);
                    }
                }
            });
        }

        function addLeadContactModal(id) {



            $("#modalLeadContact").modal('show');
            $("#formLeadContact .loadingcls").hide();
            $("#account_contact_user_id").val(id);
            $("#account_contact_id").val(0);

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
                        $("#account_contact_tag_id").val("" + responseText['data']['type']['id'] + "");
                        $('#account_contact_tag_id').append(newOption).trigger('change');
                        $('#account_contact_tag_id').trigger('change');
                        $("#account_contact_id").val(responseText['data']['id']);
                        $("#account_contact_user_id").val(responseText['data']['user_id']);
                        $("#account_contact_first_name").val(responseText['data']['first_name']);
                        $("#account_contact_last_name").val(responseText['data']['last_name']);
                        $("#account_contact_phone_number").val(responseText['data']['phone_number']);
                        $("#account_contact_alernate_phone_number").val(responseText['data'][
                            'alernate_phone_number'
                        ]);
                        $("#account_contact_email").val(responseText['data']['email']);



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


        function uploadQuotation(id) {

            $("#modalQuotation").modal('show');
            $("#formLeadQuotation .loadingcls").hide();
            $("#lead_quotation_lead_id").val(id);


        }


        function changeStatus(id) {

            var statusVal = $("#lead_status_" + id).val();

            $.ajax({
                type: 'GET',
                url: ajaxURLStatusChange + "?id=" + id + "&status=" + statusVal,
                success: function(responseText) {



                    if (responseText['status'] == 1) {

                        toastr["success"](responseText['msg']);
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

        function changeAddActionType(id) {
            var addActionType = $("#add_action_type").val();
            $("#add_action_type").val('0');
            if (addActionType == "1") {
                $('#call_notes_label').text('Call Notes')
                $('#lead_call_call_description').attr('placeholder', 'Call Notes');

                $('#lead_call_closing_note_div').addClass('d-none');
                $('#lead_call_reminder_div').removeClass('d-none');
                $('#lead_call_purpose_div').removeClass('d-none');
                $('#lead_call_status_div').addClass('d-none');
                $('#lead_call_outcome_div').addClass('d-none');
                $('#formLeadCall').trigger("reset");
                $("#modalCall").modal('show');
                $("#formLeadCall .loadingcls").hide();
                $("#lead_call_lead_id").val(id);
                $("#lead_call_id").val(0);
                $('#callFooter1 .save-btn').show();
                $('#callFooter1 .save-btn').removeClass('d-none');
                $("#lead_call_move_to_close_btn").hide();
                $('#modalCallLabel').text('Call');
                $("#lead_call_move_to_close").val(0);

                $('#lead_call_type_div, #lead_call_contact_name_div, #lead_call_call_schedule_div, #lead_call_reminder_div, #lead_call_purpose_div, #lead_call_notes_div, #select2-lead_call_type_id-container, #lead_call_call_schedule, #select2-lead_call_contact_name-container, #lead_call_call_description, #lead_call_purpose, #lead_call_reminder')
                    .removeClass('bg-light')
                $('#lead_call_call_schedule, #lead_call_reminder, #lead_call_call_description').attr('readonly', false);
                $('#pointer_event_call_type, #pointer_event_call_contact_name').removeClass('pe-none');

            } else if (addActionType == "2") {

                $('#lead_meeting_description_label').text('Meeting Notes');
                $('#lead_meeting_description').attr('placeholder', 'Meeting Notes');
                $('#lead_meeting_participants').empty().trigger('change');
                $('#lead_meeting_is_notification_div').removeClass('d-none');
                $('#formLeadMeeting').trigger("reset");

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
                $('#lead_meeting_title_div, #lead_meeting_type_div, #lead_meeting_location_div, #lead_meeting_meeting_date_time_div, #lead_meeting_is_notification_div, #lead_meeting_participants_div, #lead_meeting_note_div, #select2-lead_meeting_title_id-container, #select2-lead_meeting_type_id-container, #lead_meeting_location, #lead_meeting_meeting_date_time, #lead_meeting_reminder_id, #lead_meeting_description')
                    .removeClass('bg-light')
                $('#lead_meeting_participants_div .select2-selection--multiple').removeClass('bg-light');
                $('#lead_meeting_location, #lead_meeting_meeting_date_time, #lead_meeting_reminder_id, #lead_meeting_description')
                    .attr('readonly', false);
                $('#pointer_event_meeting_participants, #pointer_event_meeting_title, #pointer_event_meeting_type')
                    .removeClass('pe-none');

            } else if (addActionType == "3") {

                $("#lead_task_assign_to").empty().trigger('change');
                $('#formLeadTask').trigger("reset");

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
                $('#lead_task_assign_to_div, #lead_task_div, #lead_task_due_date_time_div, #lead_task_reminder_div, #lead_task_description_div, #select2-lead_task_assign_to-container, #lead_task_task, #lead_task_due_date_time, #lead_task_reminder_id, #lead_task_description')
                    .removeClass('bg-light');
                $('#lead_task_task, #lead_task_due_date_time, #lead_task_reminder_id, #lead_task_description').attr(
                    'readonly', false);
                $('#pointer_event_assign_to').removeClass('pe-none');
            }
        }

        var acc_table = $('#account_datatable').DataTable({
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
            $('#account_datatable_length').parent().removeClass().addClass(
                'col-11 d-flex justify-content-between align-items-center card-header py-2 px-2');
            $('#account_datatable_length').parent().parent().addClass('justify-content-center');
            $('#account_datatable_length label').addClass('m-0');
            $('#account_datatable_filter').parent().removeClass().addClass('col-12');
            $('#account_datatable_filter label').addClass('input-group position-relative mb-0');
            $('#account_datatable_paginate').parent().removeClass().addClass(
            'col-12 d-flex justify-content-center');
        });

        acc_table.on('xhr', function() {
            var responseData = acc_table.ajax.json();
            if (responseData['status'] == 0) {
                toastr['error'] = responseData['msg'];
            } else {
                if (viewLeadId == null || viewLeadId == "" || viewLeadId == undefined || viewLeadId == 0) {
                    getDataDetail(responseData['FirstPageLeadId']);
                } else {
                    getDataDetail(viewLeadId);
                }

                $('#list_data_loader').hide();
            }
        });


        $('#account_datatable_length').each(function() {
            $(this).before(
                '<div><i id="list_data_loader" class="bx bx-loader bx-spin font-size-16 align-middle me-2" style=""></i><b>{{ $data['title'] }}</b></div>'
            );
        });

        function reloadAccList(status = 0, isAdvanceFilter = 0) {
            acc_table.ajax.reload();
        }

        
        
    </script>

    @include('crm.lead.action_script');
@endsection
